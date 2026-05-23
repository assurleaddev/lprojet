import 'package:flutter_test/flutter_test.dart';
import 'package:usedandroid/main.dart';

void main() {
  testWidgets('App smoke test', (WidgetTester tester) async {
    await tester.pumpWidget(const UsedApp());
    expect(find.byType(UsedApp), findsOneWidget);
  });
}
