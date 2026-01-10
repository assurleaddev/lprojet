$user = \App\Models\User::first();
if ($user) {
$user->forceFill([
'email_verified_at' => now(),
'phone_verified_at' => now(),
'phone_number' => '0612345678',
'phone_country_code' => '+33'
])->save();
echo "SUCCESS: User {$user->email} (ID: {$user->id}) has been manually verified.\n";
} else {
echo "ERROR: No user found in database.\n";
}
exit;