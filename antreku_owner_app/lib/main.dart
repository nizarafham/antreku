import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'providers/auth_provider.dart';
import 'screens/login_screen.dart';
import 'screens/dashboard_screen.dart';
import 'screens/splash_screen.dart';
import 'screens/register_business_screen.dart';

void main() {
  runApp(MyApp());
}

class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (ctx) => AuthProvider(),
      child: MaterialApp(
        title: 'AntreKu Owner',
        theme: ThemeData(
          primarySwatch: Colors.blue,
          visualDensity: VisualDensity.adaptivePlatformDensity,
          scaffoldBackgroundColor: Colors.grey[100],
        ),
        home: AuthWrapper(), // Gunakan Wrapper untuk logika awal
      ),
    );
  }
}

class AuthWrapper extends StatefulWidget {
  @override
  _AuthWrapperState createState() => _AuthWrapperState();
}

class _AuthWrapperState extends State<AuthWrapper> {
  late Future<void> _authFuture;

  @override
  void initState() {
    super.initState();
    // Panggil proses auto-login HANYA SEKALI saat widget ini dibuat
    _authFuture = Provider.of<AuthProvider>(context, listen: false).tryAutoLogin();
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder(
      future: _authFuture,
      builder: (context, snapshot) {
        // SELAMA proses auto-login berjalan, tampilkan SplashScreen
        if (snapshot.connectionState == ConnectionState.waiting) {
          return SplashScreen();
        }

        // SETELAH proses auto-login selesai, gunakan Consumer untuk
        // menentukan halaman berdasarkan state terakhir.
        return Consumer<AuthProvider>(
          builder: (ctx, auth, _) {
            if (auth.isAuthenticated) {
              if (auth.user?.business != null) {
                return DashboardScreen();
              } else {
                return RegisterBusinessScreen();
              }
            }
            return LoginScreen();
          },
        );
      },
    );
  }
}