import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import 'dart:async';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../models/queue_model.dart';
import '../models/business_model.dart';

class DashboardScreen extends StatefulWidget {
  @override
  _DashboardScreenState createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  late Future<Map<String, dynamic>> _dashboardFuture;
  final ApiService _apiService = ApiService();
  Timer? _timer;
  bool _isBusinessOpen = true;
  bool _isToggleLoading = false;

  @override
  void initState() {
    super.initState();
    _loadDashboard();
    _timer = Timer.periodic(Duration(seconds: 30), (timer) => _loadDashboard());
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  Future<void> _loadDashboard() async {
    setState(() {
      _dashboardFuture = _apiService.getTodaysQueues();
    });
    _dashboardFuture.then((data) {
      if (mounted) {
        setState(() {
          _isBusinessOpen = data['business_status']['is_open'];
        });
      }
    }).catchError((_) {
      // Handle error if needed, e.g., set a default state
    });
  }
  
  Future<void> _toggleBusinessStatus() async {
    setState(() => _isToggleLoading = true);
    try {
      final newStatus = await _apiService.toggleBusinessStatus();
      if (mounted) {
        setState(() => _isBusinessOpen = newStatus);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Status toko berhasil diubah!'), backgroundColor: Colors.green),
        );
      }
    } catch(e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal mengubah status.'), backgroundColor: Colors.red),
        );
      }
    } finally {
      if(mounted) setState(() => _isToggleLoading = false);
    }
  }

  void _showActionConfirmation(BuildContext context, Queue queue, String action) {
    String title = '';
    String content = '';
    switch (action) {
      case 'call':
        title = 'Panggil Pelanggan?';
        content = 'Anda akan memanggil ${queue.customer.name} (No. ${queue.queueNumber}).';
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
            child: Text('Ya, Lanjutkan', style: TextStyle(color: Colors.white)),
            onPressed: () {
              Navigator.of(ctx).pop();
              _updateStatus(queue.id, action);
            },
            style: ElevatedButton.styleFrom(backgroundColor: action == 'no-show' ? Colors.red.shade600 : Colors.blue.shade700),
          ),
        ],
      ),
    );
  }

  Future<void> _updateStatus(int queueId, String action) async {
    try {
      await _apiService.updateQueueStatus(queueId, action);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Status berhasil diperbarui!'), backgroundColor: Colors.green.shade700),
        );
      }
      _loadDashboard();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal memperbarui status.'), backgroundColor: Colors.red.shade700),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    return Scaffold(
      backgroundColor: Colors.grey[100],
      appBar: AppBar(
        backgroundColor: Colors.blue.shade800,
        foregroundColor: Colors.white,
        title: Text('Dashboard Bisnis'),
        actions: [
          IconButton(
            icon: Icon(Icons.logout),
            tooltip: 'Logout',
            onPressed: () {
              authProvider.logout();
            },
          ),
        ],
      ),
      body: FutureBuilder<Map<String, dynamic>>(
        future: _dashboardFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return Center(child: CircularProgressIndicator());
          } 
          
          if (snapshot.hasError) {
            return _buildErrorState();
          } 
          
          final List<Queue> queues = (snapshot.data!['queues'] as List)
              .map((q) => Queue.fromJson(q)).toList();
          
          final Business businessStatus = Business.fromJson(snapshot.data!['business_status']);
          _isBusinessOpen = businessStatus.isOpen;

          return RefreshIndicator(
            onRefresh: _loadDashboard,
            child: Column(
              children: [
                _buildBusinessStatusCard(),
                _buildSummaryCard(queues),
                _buildNowServingCard(queues),
                if (queues.isEmpty)
                  Expanded(child: _buildEmptyState())
                else
                  Expanded(child: _buildQueueList(queues)),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildBusinessStatusCard() {
    return Card(
      margin: EdgeInsets.fromLTRB(12, 12, 12, 0),
      color: Colors.blue.shade700,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              _isBusinessOpen ? 'Antrean Dibuka' : 'Antrean Ditutup',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.white),
            ),
            _isToggleLoading 
              ? SizedBox(width: 24, height: 24, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 3))
              : Switch(
                  value: _isBusinessOpen,
                  onChanged: (value) => _toggleBusinessStatus(),
                  activeTrackColor: Colors.lightGreenAccent,
                  activeColor: Colors.green.shade800,
                ),
          ],
        ),
      ),
    );
  }

  Widget _buildNowServingCard(List<Queue> queues) {
    final nowServing = queues.firstWhere(
      (q) => q.status == 'called' || q.status == 'in_progress',
      orElse: () => Queue(id: 0, queueNumber: 0, scheduledAt: '', status: '', customer: Customer(id: 0, name: '', phoneNumber: '')),
    );

    return Card(
      margin: EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text('Sedang Dilayani', textAlign: TextAlign.center, style: TextStyle(fontSize: 16, color: Colors.grey[600])),
            SizedBox(height: 8),
            Text(
              nowServing.queueNumber == 0 ? '-' : nowServing.queueNumber.toString(),
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 48, fontWeight: FontWeight.bold, color: Colors.blue.shade800),
            ),
            SizedBox(height: 4),
            Text(
              nowServing.queueNumber == 0 ? 'Belum ada pelanggan yang dipanggil' : nowServing.customer.name,
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
            )
          ],
        ),
      ),
    );
  }

  Widget _buildSummaryCard(List<Queue> queues) {
    int waiting = queues.where((q) => q.status == 'confirmed' || q.status == 'called').length;
    int completed = queues.where((q) => q.status == 'completed').length;
    return Card(
      margin: EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceAround,
          children: [
            _summaryItem(Icons.people_outline, 'Total Hari Ini', queues.length, Colors.blue),
            _summaryItem(Icons.hourglass_top_rounded, 'Menunggu', waiting, Colors.orange),
            _summaryItem(Icons.check_circle_outline, 'Selesai', completed, Colors.green),
          ],
        ),
      ),
    );
  }

  Widget _summaryItem(IconData icon, String label, int count, Color color) {
    return Column(
      children: [
        Icon(icon, color: color, size: 30),
        SizedBox(height: 4),
        Text(count.toString(), style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: color)),
        SizedBox(height: 2),
        Text(label, style: TextStyle(fontSize: 12, color: Colors.grey[600])),
      ],
    );
  }

  Widget _buildQueueList(List<Queue> queues) {
    return ListView.builder(
      padding: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      itemCount: queues.length,
      itemBuilder: (ctx, index) {
        final queue = queues[index];
        return Card(
          margin: EdgeInsets.symmetric(vertical: 6),
          elevation: 2,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
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
                      style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: Colors.blue.shade800),
                    ),
                    Container(
                      padding: EdgeInsets.symmetric(horizontal: 12, vertical: 5),
                      decoration: BoxDecoration(
                        color: _getStatusColor(queue.status).withOpacity(0.15),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        queue.status.replaceAll('_', ' ').toUpperCase(),
                        style: TextStyle(color: _getStatusColor(queue.status), fontWeight: FontWeight.bold, fontSize: 12),
                      ),
                    ),
                  ],
                ),
                Divider(height: 20),
                Text(
                  queue.customer.name,
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
                ),
                SizedBox(height: 6),
                Row(
                  children: [
                    Icon(Icons.phone_outlined, size: 16, color: Colors.grey[600]),
                    SizedBox(width: 8),
                    Text(queue.customer.phoneNumber, style: TextStyle(color: Colors.grey.shade700, fontSize: 14)),
                  ],
                ),
                SizedBox(height: 6),
                Row(
                  children: [
                    Icon(Icons.access_time_rounded, size: 16, color: Colors.grey[600]),
                    SizedBox(width: 8),
                    Text(
                      'Jadwal: ${DateFormat('HH:mm').format(DateTime.parse(queue.scheduledAt))}',
                      style: TextStyle(color: Colors.grey.shade700, fontSize: 14),
                    ),
                  ],
                ),
                SizedBox(height: 16),
                _buildActionButtons(queue),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildActionButtons(Queue queue) {
    bool isFinalState = queue.status == 'completed' || queue.status == 'no_show' || queue.status == 'cancelled';
    
    return Column(
      children: [
        SizedBox(
          width: double.infinity,
          child: ElevatedButton.icon(
            icon: Icon(Icons.campaign_outlined, color: Colors.white),
            label: Text('Panggil Pelanggan', style: TextStyle(color: Colors.white, fontSize: 16)),
            onPressed: queue.status != 'confirmed' ? null : () => _showActionConfirmation(context, queue, 'call'),
            style: ElevatedButton.styleFrom(
              padding: EdgeInsets.symmetric(vertical: 12),
              backgroundColor: Colors.blue.shade700,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
          ),
        ),
        SizedBox(height: 8),
        Row(
          children: [
            Expanded(
              child: OutlinedButton.icon(
                icon: Icon(Icons.check_circle_outline),
                label: Text('Selesai'),
                onPressed: isFinalState ? null : () => _showActionConfirmation(context, queue, 'complete'),
                style: OutlinedButton.styleFrom(foregroundColor: Colors.green.shade700, side: BorderSide(color: Colors.green.shade700)),
              ),
            ),
            SizedBox(width: 8),
            Expanded(
              child: OutlinedButton.icon(
                icon: Icon(Icons.cancel_outlined),
                label: Text('Tak Hadir'),
                onPressed: isFinalState ? null : () => _showActionConfirmation(context, queue, 'no-show'),
                style: OutlinedButton.styleFrom(foregroundColor: Colors.red.shade700, side: BorderSide(color: Colors.red.shade700)),
              ),
            ),
          ],
        ),
      ],
    );
  }
  
  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.event_note_rounded, size: 100, color: Colors.grey[300]),
          SizedBox(height: 20),
          Text('Belum Ada Antrean', style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Colors.grey[700])),
          SizedBox(height: 8),
          Text('Antrean untuk hari ini akan muncul di sini.', style: TextStyle(fontSize: 16, color: Colors.grey[500])),
          SizedBox(height: 20),
          ElevatedButton.icon(
            icon: Icon(Icons.refresh, color: Colors.white),
            label: Text('Coba Lagi', style: TextStyle(color: Colors.white)),
            onPressed: _loadDashboard,
            style: ElevatedButton.styleFrom(backgroundColor: Colors.blue.shade700),
          )
        ],
      ),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(20.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.cloud_off_rounded, size: 100, color: Colors.red[300]),
            SizedBox(height: 20),
            Text('Oops! Terjadi Masalah', style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Colors.grey[700])),
            SizedBox(height: 8),
            Text('Tidak dapat terhubung ke server. Pastikan koneksi internet Anda stabil dan server berjalan.', textAlign: TextAlign.center, style: TextStyle(fontSize: 16, color: Colors.grey[500])),
            SizedBox(height: 20),
            ElevatedButton.icon(
              icon: Icon(Icons.refresh, color: Colors.white),
              label: Text('Coba Lagi', style: TextStyle(color: Colors.white)),
              onPressed: _loadDashboard,
              style: ElevatedButton.styleFrom(backgroundColor: Colors.blue.shade700),
            )
          ],
        ),
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'confirmed': return Colors.orange.shade700;
      case 'called': return Colors.blue.shade700;
      case 'in_progress': return Colors.blue.shade700;
      case 'completed': return Colors.green.shade700;
      case 'no_show': return Colors.red.shade700;
      case 'cancelled': return Colors.grey.shade600;
      default: return Colors.grey.shade600;
    }
  }
}