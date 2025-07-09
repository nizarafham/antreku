class Business {
  final int id;
  final String name;
  final bool isOpen;

  Business({required this.id, required this.name, required this.isOpen});

  factory Business.fromJson(Map<String, dynamic> json) {
    return Business(
      id: json['id'],
      name: json['name'],
      isOpen: json['is_open'] ?? true, // Default ke true jika null
    );
  }
}