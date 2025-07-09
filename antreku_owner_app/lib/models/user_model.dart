import 'business_model.dart';

class User {
  final int id;
  final String name;
  final String email;
  final Business? business;

  User({
    required this.id,
    required this.name,
    required this.email,
    this.business,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      name: json['name'],
      email: json['email'],
      business: json['business'] != null ? Business.fromJson(json['business']) : null,
    );
  }
}