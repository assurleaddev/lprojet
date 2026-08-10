# Data Minimization & Retention Policy

**Application:** Used (usedis.com) — second-hand fashion marketplace  
**Last updated:** 2026-06-25  
**Standard:** Orange Group MINI Apps Security Requirements — REQ.PRIV.2, REQ.PRIV.3

---

## 1. Principles

- Collect only data strictly necessary to provide the service (data minimization).
- Retain data only for the period required by operational or legal need, then delete it.
- Never log or store credentials, payment card data, or tokens in plaintext.

---

## 2. Data Collected and Purpose

| Data field | Model / Table | Purpose | Legal basis |
|---|---|---|---|
| `email` | `users` | Account identity, login, password reset | Contract |
| `first_name`, `last_name` | `users` | Display name, seller/buyer identity | Contract |
| `phone` | `users` | Optional 2FA verification | Legitimate interest |
| `password` (hashed, bcrypt) | `users` | Authentication | Contract |
| `wallet_balance` | `users` | Seller payout tracking | Contract |
| `profile_photo` | media files | Public seller profile | Consent |
| Product listing data | `products` | Core marketplace function | Contract |
| Product images | media files | Listing display | Contract |
| `offer.amount`, `offer.status` | `offers` | Transaction negotiation | Contract |
| `order.status`, `order.payout_amount` | `orders` | Order fulfillment, seller payout | Contract |
| `carrier`, `tracking_code` | `orders` | Shipping transparency to buyer | Contract |
| IP address | `action_logs` | Security audit trail | Legitimate interest |
| `user_agent` | auth event logs | Security audit trail | Legitimate interest |
| Chat messages | `messages` | Buyer-seller communication | Contract |

---

## 3. Data NOT Collected

- Payment card numbers, CVV, PINs — no card data ever passes through the application.
- GPS/location — the mobile app does not request or store location data.
- Contacts — the mobile app does not access the device contact list.
- Browsing history outside the app.

---

## 4. Sensitive Data Handling

- Passwords are hashed with bcrypt before storage; plaintext is never persisted.
- Auth tokens are opaque (Sanctum) and do not encode personal data.
- Mobile tokens are stored in OS-level secure storage (Android Keystore / iOS Keychain).
- All log entries are processed by `SanitizeProcessor` which redacts: `password`, `token`,
  `access_token`, `api_key`, `secret`, `pin`, `cvv`, `card_number` → `[REDACTED]`.

---

## 5. Retention Periods

| Data category | Retention | Mechanism |
|---|---|---|
| Active user accounts | Until account deletion requested | Manual or future right-to-erasure flow |
| Action logs (`action_logs`) | 90 days | `app:purge-expired-data --days=90` (daily at 04:00) |
| Sanctum tokens (`personal_access_tokens`) | 90 days from creation | Same purge command |
| Application log files (`storage/logs/`) | 90 days | Daily channel rotation (`'days' => 90` in `config/logging.php`) |
| Chat messages | Duration of order lifecycle | No automatic purge yet — review required |
| Product listings (sold/deleted) | 90 days after deletion | No automatic purge yet — review required |

---

## 6. Third-Party Data Processors

| Processor | Data shared | Purpose |
|---|---|---|
| Google (OAuth) | Email, name | Social login |
| Twilio | Phone number | OTP / 2FA |
| Agora | User ID, session token | In-app voice/video (if enabled) |
| OpenAI | Product description text | AI content generation |
| Mail provider (Gmail SMTP) | Email address, message content | Transactional emails |

Each processor must have a Data Processing Agreement (DPA) in place before production use.

---

## 7. User Rights

Users may request access to, correction of, or deletion of their personal data by contacting the data controller. A formal right-to-erasure (GDPR Art. 17) flow is pending implementation (tracked as REQ.PRIV.1).
