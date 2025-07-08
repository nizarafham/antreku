import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:antreku_owner_app/models/queue_model.dart';

class ApiService {
  // GANTI DENGAN URL API LARAVEL ANDA
  static const String _baseUrl = 'http://127.0.0.1:8000/api/owner';

  static Future<Map<String, String>> _getHeaders() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    if (token != null) {
      return {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      };
    }
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
  }

  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$_baseUrl/login'),
      headers: await _getHeaders(),
      body: jsonEncode({'email': email, 'password': password}),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Failed to login: ${response.body}');
    }
  }
  
  Future<List<Queue>> getTodaysQueues() async {
    final response = await http.get(
      Uri.parse('$_baseUrl/dashboard/queues'),
      headers: await _getHeaders(),
    );

    if (response.statusCode == 200) {
      List<dynamic> data = jsonDecode(response.body);
      return data.map((json) => Queue.fromJson(json)).toList();
    } else {
      throw Exception('Failed to load queues');
    }
  }

  Future<void> updateQueueStatus(int queueId, String action) async {
    // action bisa 'call', 'complete', atau 'no-show'
    final response = await http.post(
      Uri.parse('$_baseUrl/dashboard/queues/$queueId/$action'),
      headers: await _getHeaders(),
    );

    if (response.statusCode != 200) {
      throw Exception('Failed to update queue status: ${response.body}');
    }
  }
}


