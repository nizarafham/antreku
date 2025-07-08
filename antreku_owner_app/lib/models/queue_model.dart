class Queue {
  final int id;
  final int queueNumber;
  final String scheduledAt;
  final String status;
  final Customer customer;

  Queue({
    required this.id,
    required this.queueNumber,
    required this.scheduledAt,
    required this.status,
    required this.customer,
  });

  factory Queue.fromJson(Map<String, dynamic> json) {
    return Queue(
      id: json['id'],
      queueNumber: json['queue_number'],
      scheduledAt: json['scheduled_at'],
      status: json['status'],
      customer: Customer.fromJson(json['customer']),
    );
  }
}

class Customer {
  final int id;
  final String name;
  final String phoneNumber;

  Customer({required this.id, required this.name, required this.phoneNumber});

  factory Customer.fromJson(Map<String, dynamic> json) {
    return Customer(
      id: json['id'],
      name: json['name'],
      phoneNumber: json['phone_number'],
    );
  }
}