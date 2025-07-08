import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/api_service.dart';

class AuthProvider with ChangeNotifier {
  String? _token;
  String? _userName;
  bool _isAuthenticated = false;
  bool _isLoading = true; // Add loading state

  String? get token => _token;
  String? get userName => _userName;
  bool get isAuthenticated => _isAuthenticated;
  bool get isLoading => _isLoading; // Add getter for loading state

  final ApiService _apiService = ApiService();

  Future<void> login(String email, String password) async {
    try {
      final response = await _apiService.login(email, password);
      _token = response['token'];
      _userName = response['user']['name'];
      _isAuthenticated = true;
      _isLoading = false;

      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('token', _token!);
      await prefs.setString('userName', _userName!);
      
      print('AuthProvider: Login successful for user: $_userName');
      notifyListeners();
    } catch (e) {
      _isAuthenticated = false;
      _isLoading = false;
      print('AuthProvider: Login failed: $e');
      notifyListeners();
      throw e;
    }
  }

  Future<void> tryAutoLogin() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      if (!prefs.containsKey('token')) {
        _isAuthenticated = false;
        _isLoading = false;
        print('AuthProvider: No token found in SharedPreferences. Not authenticated.');
        notifyListeners();
        return;
      }
      
      _token = prefs.getString('token');
      _userName = prefs.getString('userName');
      _isAuthenticated = true;
      _isLoading = false;
      print('AuthProvider: Auto-login successful for user: $_userName');
      notifyListeners();
    } catch (e) {
      _isAuthenticated = false;
      _isLoading = false;
      print('AuthProvider: Error during auto-login: $e');
      notifyListeners();
    }
  }

  Future<void> logout() async {
    _token = null;
    _userName = null;
    _isAuthenticated = false;
    _isLoading = false;
    final prefs = await SharedPreferences.getInstance();
    await prefs.clear();
    print('AuthProvider: User logged out and SharedPreferences cleared.');
    notifyListeners();
  }
}