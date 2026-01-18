# API & Route Documentation - RestoBook

Документація маршрутів вебзастосунок та зовнішніх API-інтеграцій.

- **Base URL (Local):** `http://localhost:8000`
- **Base URL (Public):** `https://your-ngrok-url.ngrok-free.dev`
- **Authentication:** Session / Cookies (Laravel Auth)


## 1. Зовнішні Webhooks (External API)
Ці маршрути приймають `JSON` від зовнішніх сервісів. Це єдина частина системи, що працює як класичний REST API.

### Monobank: Отримання статусу оплати
Обробляє callback від банку після оплати VIP-статусу.

- **URL:** `/monobank/subscription/webhook`
- **Method:** `POST`
- **Headers:** `Content-Type: application/json`

**Приклад тіла запиту (Request Body):**
```json
{
  "invoiceId": "260118CuZpz1JGRGPjLz",
  "status": "success",
  "amount": 20000,
  "ccy": 980,
  "finalAmount": 20000,
  "merchantPaymInfo": {
      "reference": "vip_user_23",
      "destination": "Оплата VIP-підписки"
  },
  "createdDate": "2026-01-18T13:05:07Z",
  "modifiedDate": "2026-01-18T13:05:13Z"
}
```

##  2. Управління бронюваннями (Internal Routes)
Маршрути, які використовує вебінтерфейс для створення та управління бронюваннями.

### Сторінка бронювання
Повертає HTML-форму для вибору ресторану та столика.
- **URL:** `/booking`
- **Method:** `GET`
- **Auth:** Required

### Створити бронювання
Обробляє форму, перевіряє доступність столика на вибраний час та зберігає запис у базі даних.
- **URL:** `/booking`
- **Method:** `POST`
- **Auth:** Required

**Параметри запиту (Form Data):**

| Назва           | Тип  | Обов'язково | Опис                       |
|:----------------|:-----|:------------|:---------------------------|
| `restaurant_id` | int  | Так         | ID ресторану               |
| `table_id`      | int  | Так         | ID столика                 |
| `date`          | date | Так         | Дата візиту (формат Y-m-d) |
| `start_time`    | time | Так         | Час початку (H:i)          |
| `end_time`      | time | Так         | Час завершення (H:i)       |


### Скасувати бронювання
Видаляє бронювання або змінює його статус на `cancelled` (скасовано).
- **URL:** `/booking/{id}`
- **Method:** `DELETE`
- **Auth:** Required (Тільки власник бронювання)


##  3. Інтеграція з Telegram Bot

###  Сторінка підключення
Відображає інструкцію та унікальний код для підключення бота.
- **URL:** `/telegram`
- **Method:** `GET`
- **Auth:** Required

### Перевірка підключення
Цей маршрут викликається кнопкою "Перевірити підключення", щоб зв'язати акаунт сайту з Telegram-акаунтом.
- **URL:** `/telegram/check`
- **Method:** `POST`
- **Auth:** Required

**Логіка роботи:**
1. Система перевіряє останні повідомлення бота (через метод `getUpdates` або Webhook).
2. Шукає повідомлення з текстом `connect-{user_id}`.
3. Якщо повідомлення знайдено — зберігає `chat_id` Telegram у таблицю користувачів.


## 4. VIP Система (Payments)

### Ініціалізація оплати
Генерує посилання на оплату та перенаправляє клієнта на сторінку еквайрингу.
- **URL:** `/subscription/pay`
- **Method:** `GET`
- **Auth:** Required

**Логіка роботи:**
1. Генерує запит до Monobank API (`/api/merchant/invoice/create`).
2. Передає параметр `merchantPaymInfo.reference` з ID поточного користувача.
3. Отримує `pageUrl` від банку.
4. Здійснює редірект користувача на отриману URL-адресу.



##  5. Автентифікація (Auth)
Стандартні маршрути Laravel Breeze / UI для керування сесіями.

| Метод  | URL         | Опис                                  |
|:-------|:------------|:--------------------------------------|
| `GET`  | `/login`    | Відображення форми входу              |
| `POST` | `/login`    | Обробка даних входу                   |
| `GET`  | `/register` | Відображення форми реєстрації         |
| `POST` | `/register` | Обробка реєстрації нового користувача |
| `POST` | `/logout`   | Вихід із системи (знищення сесії)     |

##  6. Структура даних (Models)
Приклади основних об'єктів JSON, що використовуються в системі.

**User (Користувач):**
```json
{
  "id": 1,
  "name": "Клієнт 1",
  "email": "client1@vexample.com",
  "phone": "+380500000001",
  "telegram_chat_id": "(NULL)",
  "subscription_ends_at": "2026-02-18 15:00:00",
  "vip_notification_sent": 0,
  "email_verified_at": "(NULL)",
  "password": "$2y$12$ooPjnZh4aaSODrDQc2pnzuHdarqeRxt7jRanCr2HQSWTnISHXMwdO",
  "remember_token": "(NULL)" 
}
```
**Booking (Бронювання):**
```json
{
  "id": 45,
  "user_id": 1,
  "table_id": 46,
  "start_time": "2026-01-20 18:00:00",
  "end_time": "2026-01-20 20:00:00",
  "status": "confirmed", 
  "reminder_sent": 0,
  "invoice_id": "(NULL)"  
}
```
