import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/business_model.dart';
import '../models/user_model.dart';
import '../services/api_service.dart';

class AuthProvider with ChangeNotifier {
  String? _token;
  User? _user;
  bool _isAuthenticated = false;

  String? get token => _token;
  User? get user => _user;
  bool get isAuthenticated => _isAuthenticated;

  final ApiService _apiService = ApiService();

  Future<void> login(String email, String password) async {
    final response = await _apiService.login(email, password);
    _token = response['token'];
    _user = User.fromJson(response['user']);
    _isAuthenticated = true;

    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('token', _token!);
    await prefs.setString('user', jsonEncode(response['user']));
    
    notifyListeners();
  }

  Future<void> tryAutoLogin() async {
    final prefs = await SharedPreferences.getInstance();
    if (!prefs.containsKey('token') || !prefs.containsKey('user')) {
      _isAuthenticated = false;
      return;
    }
    
    _token = prefs.getString('token');
    _user = User.fromJson(jsonDecode(prefs.getString('user')!));
    _isAuthenticated = true;
    notifyListeners();
  }
  
  void updateUserWithBusiness(Business newBusiness) {
    if (_user != null) {
      _user = User(
        id: _user!.id,
        name: _user!.name,
        email: _user!.email,
        business: newBusiness,
      );
      SharedPreferences.getInstance().then((prefs) {
        prefs.setString('user', jsonEncode({
          'id': _user!.id, 
          'name': _user!.name, 
          'email': _user!.email, 
          'business': {
            'id': newBusiness.id, 
            'name': newBusiness.name, 
            'is_open': newBusiness.isOpen
          }
        }));
      });
      notifyListeners();
    }
  }

  Future<void> logout() async {
    _token = null;
    _user = null; // <-- Perbaikan di sini
    _isAuthenticated = false;
    final prefs = await SharedPreferences.getInstance();
    await prefs.clear();
    notifyListeners();
  }
}