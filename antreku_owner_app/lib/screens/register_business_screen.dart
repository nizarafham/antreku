import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';

class RegisterBusinessScreen extends StatefulWidget {
  @override
  _RegisterBusinessScreenState createState() => _RegisterBusinessScreenState();
}

class _RegisterBusinessScreenState extends State<RegisterBusinessScreen> {
  final _formKey = GlobalKey<FormState>();
  final _apiService = ApiService();
  bool _isLoading = false;
  
  final _nameController = TextEditingController();
  final _slugController = TextEditingController();
  final _addressController = TextEditingController();
  final _dpController = TextEditingController();

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    
    setState(() => _isLoading = true);

    try {
      final businessData = {
        'name': _nameController.text,
        'slug': _slugController.text,
        'address': _addressController.text,
        'dp_percentage': int.parse(_dpController.text),
      };
      
      final newBusiness = await _apiService.createBusiness(businessData);
      
      // Update provider agar app tahu user sudah punya bisnis
      Provider.of<AuthProvider>(context, listen: false).updateUserWithBusiness(newBusiness);
      // Navigasi ke dashboard akan ditangani oleh Consumer di main.dart

    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal mendaftarkan bisnis: ${e.toString()}'), backgroundColor: Colors.red)
      );
    } finally {
      if(mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Daftarkan Bisnis Anda')),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(24),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Text('Langkah Terakhir!', style: Theme.of(context).textTheme.headlineSmall),
              Text('Lengkapi detail bisnis Anda untuk memulai.', style: Theme.of(context).textTheme.titleMedium),
              SizedBox(height: 24),
              TextFormField(
                controller: _nameController,
                decoration: InputDecoration(labelText: 'Nama Bisnis', border: OutlineInputBorder()),
                validator: (v) => v!.isEmpty ? 'Nama bisnis tidak boleh kosong' : null,
              ),
              SizedBox(height: 16),
              TextFormField(
                controller: _slugController,
                decoration: InputDecoration(labelText: 'URL Slug', hintText: 'contoh: barbershop-keren', border: OutlineInputBorder()),
                validator: (v) => v!.isEmpty ? 'Slug tidak boleh kosong' : null,
              ),
              SizedBox(height: 16),
              TextFormField(
                controller: _addressController,
                decoration: InputDecoration(labelText: 'Alamat Lengkap', border: OutlineInputBorder()),
                validator: (v) => v!.isEmpty ? 'Alamat tidak boleh kosong' : null,
              ),
              SizedBox(height: 16),
              TextFormField(
                controller: _dpController,
                decoration: InputDecoration(labelText: 'Persentase DP (%)', hintText: 'Contoh: 50', border: OutlineInputBorder()),
                keyboardType: TextInputType.number,
                validator: (v) {
                  if (v!.isEmpty) return 'DP tidak boleh kosong';
                  if (int.tryParse(v) == null) return 'Masukkan angka yang valid';
                  return null;
                },
              ),
              SizedBox(height: 24),
              _isLoading
                ? Center(child: CircularProgressIndicator())
                : ElevatedButton(
                    onPressed: _submit,
                    child: Text('Simpan & Mulai'),
                    style: ElevatedButton.styleFrom(padding: EdgeInsets.symmetric(vertical: 16)),
                  ),
            ],
          ),
        ),
      ),
    );
  }
}