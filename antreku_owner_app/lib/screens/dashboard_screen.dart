import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../models/queue_model.dart';

class DashboardScreen extends StatefulWidget {
  @override
  _DashboardScreenState createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  late Future<List<Queue>> _queuesFuture;
  final ApiService _apiService = ApiService();
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _queuesFuture = _apiService.getTodaysQueues();
    // Auto-refresh data setiap 30 detik
    _timer = Timer.periodic(Duration(seconds: 30), (timer) {
      setState(() {
        _queuesFuture = _apiService.getTodaysQueues();
      });
    });
  }

  @override
  void dispose() {
    _timer?.cancel(); // Hentikan timer saat widget dihancurkan
    super.dispose();
  }

  Future<void> _refreshQueues() async {
    setState(() {
      _queuesFuture = _apiService.getTodaysQueues();
    });
  }
  
  void _showActionConfirmation(BuildContext context, Queue queue, String action) {
    String title = '';
    String content = '';
    switch (action) {
      case 'call':
        title = 'Panggil Pelanggan?';
        content = 'Anda akan memanggil ${queue.customer.name} dengan nomor antrean ${queue.queueNumber}.';
        break;
      case 'complete':
        title = 'Selesaikan Antrean?';
        content = 'Antrean untuk ${queue.customer.name} akan ditandai sebagai selesai.';
        break;
      case 'no-show':
        title = 'Tandai Tidak Hadir?';
        content = 'Antrean untuk ${queue.customer.name} akan ditandai tidak hadir dan DP hangus.';
        break;
    }

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(title),
        content: Text(content),
        actions: <Widget>[
          TextButton(
            child: Text('Batal'),
            onPressed: () => Navigator.of(ctx).pop(),
          ),
          ElevatedButton(
            child: Text('Ya, Lanjutkan'),
            onPressed: () {
              Navigator.of(ctx).pop();
              _updateStatus(queue.id, action);
            },
            style: ElevatedButton.styleFrom(backgroundColor: action == 'no-show' ? Colors.red : Colors.blue),
          ),
        ],
      ),
    );
  }

  Future<void> _updateStatus(int queueId, String action) async {
    try {
      await _apiService.updateQueueStatus(queueId, action);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Status berhasil diperbarui!'), backgroundColor: Colors.green),
      );
      _refreshQueues();
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal memperbarui status.'), backgroundColor: Colors.red),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    return Scaffold(
      appBar: AppBar(
        title: Text('Dashboard Antrean'),
        actions: [
          IconButton(
            icon: Icon(Icons.logout),
            onPressed: () {
              authProvider.logout();
            },
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _refreshQueues,
        child: FutureBuilder<List<Queue>>(
          future: _queuesFuture,
          builder: (context, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting) {
              return Center(child: CircularProgressIndicator());
            } else if (snapshot.hasError) {
              return Center(child: Text('Gagal memuat data: ${snapshot.error}'));
            } else if (!snapshot.hasData || snapshot.data!.isEmpty) {
              return Center(child: Text('Belum ada antrean untuk hari ini.'));
            }

            final queues = snapshot.data!;
            return ListView.builder(
              itemCount: queues.length,
              itemBuilder: (ctx, index) {
                final queue = queues[index];
                return Card(
                  margin: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  elevation: 3,
                  child: Padding(
                    padding: const EdgeInsets.all(16.0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              'No. ${queue.queueNumber}',
                              style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Colors.blue.shade700),
                            ),
                            Container(
                              padding: EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                              decoration: BoxDecoration(
                                color: _getStatusColor(queue.status).withOpacity(0.2),
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Text(
                                queue.status.replaceAll('_', ' ').toUpperCase(),
                                style: TextStyle(color: _getStatusColor(queue.status), fontWeight: FontWeight.bold, fontSize: 12),
                              ),
                            ),
                          ],
                        ),
                        SizedBox(height: 12),
                        Text(
                          queue.customer.name,
                          style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
                        ),
                        SizedBox(height: 4),
                        Text(queue.customer.phoneNumber, style: TextStyle(color: Colors.grey.shade600)),
                        SizedBox(height: 4),
                        Text(
                          'Jadwal: ${DateFormat('HH:mm').format(DateTime.parse(queue.scheduledAt))}',
                          style: TextStyle(color: Colors.grey.shade600),
                        ),
                        Divider(height: 24),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceAround,
                          children: [
                            TextButton.icon(
                              icon: Icon(Icons.check_circle, color: Colors.green),
                              label: Text('Selesai'),
                              onPressed: queue.status == 'completed' || queue.status == 'no_show' ? null : () => _showActionConfirmation(context, queue, 'complete'),
                            ),
                            TextButton.icon(
                              icon: Icon(Icons.cancel, color: Colors.red),
                              label: Text('Tak Hadir'),
                              onPressed: queue.status == 'completed' || queue.status == 'no_show' ? null : () => _showActionConfirmation(context, queue, 'no-show'),
                            ),
                          ],
                        ),
                        SizedBox(height: 8),
                        SizedBox(
                          width: double.infinity,
                          child: ElevatedButton.icon(
                            icon: Icon(Icons.campaign),
                            label: Text('Panggil Pelanggan'),
                            onPressed: queue.status != 'confirmed' ? null : () => _showActionConfirmation(context, queue, 'call'),
                            style: ElevatedButton.styleFrom(
                              padding: EdgeInsets.symmetric(vertical: 12),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            );
          },
        ),
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'confirmed':
        return Colors.orange;
      case 'called':
      case 'in_progress':
        return Colors.blue;
      case 'completed':
        return Colors.green;
      case 'no_show':
      case 'cancelled':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }
}
