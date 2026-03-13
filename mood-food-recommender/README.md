# Mood-Based Recipe Recommender 🍽️

A responsive, premium SaaS web application that recommends recipes based on user mood with multi-cuisine support and filtering capabilities.

## Features

- **Mood-Based Recommendations**: Select from **9 moods** (Happy, Stressed, Tired, Bored, Romantic, Angry, Relaxed, Rainy, Sunny) to get personalized recipe suggestions
- **Multi-Cuisine Support**: Recipes from Nepali, Indian, American, Chinese, Japanese, Italian, and Coffee Shop cuisines
- **Smart Filtering**: Filter by vegetarian, high-protein preferences
- **Recipe Details**: Full recipe pages with ingredients, step-by-step instructions, and nutrition information
- **User Authentication**: Register, login, and save favorite recipes
- **Premium Features**: Subscription model with premium features (unlimited saved recipes, custom meal plans)
- **Responsive Design**: Mobile-first design that works on all screen sizes (320px+)
- **Beautiful UI**: Cute baby pink theme with high contrast for accessibility
- **Premium Welcome Sticker**: Cloud callout design with smooth animations
- **33 Recipes**: Comprehensive recipe collection across all moods

## Tech Stack

- **Backend**: PHP 7.4+ (vanilla, no frameworks)
- **Database**: MySQL 5.7+ / MariaDB 10.2+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **UI Framework**: Bootstrap 5
- **Server**: Apache/Nginx (LAMP stack)

### Recommended Local XAMPP Setup

- Apache: port **80**
- MySQL: port **3307**
- Database: `mood_food_recommender`
- DB user: `root` (no password)

## Project Structure

```
mood-food-recommender/
├── api/                    # API endpoints
│   ├── auth.php           # Authentication (register/login/logout)
│   ├── moods.php          # List all moods
│   ├── suggestions.php    # Get recipe suggestions
│   ├── recipe.php         # Get recipe details
│   ├── subscription.php   # Subscription management
│   └── admin/             # Admin endpoints
│       └── recipes.php    # CRUD operations
├── assets/
│   ├── css/
│   │   └── main.css       # Main stylesheet (baby pink theme)
│   ├── js/
│   │   └── app.js         # Main JavaScript
│   └── images/            # Recipe images (optional)
├── admin/                  # Admin panel (dashboard, CRUD, analytics)
│   ├── assets/
│   │   ├── css/admin.css
│   │   └── js/admin.js
│   ├── includes/           # Admin layout + helpers
│   │   ├── config.php
│   │   ├── header.php
│   │   ├── sidebar.php
│   │   └── footer.php
│   ├── dashboard.php
│   ├── login.php
│   ├── logout.php
│   ├── recipes.php
│   ├── add_recipe.php
│   ├── moods.php
│   ├── users.php
│   ├── analytics.php
│   └── settings.php
├── config/
│   ├── config.php         # Application configuration
│   └── database.php       # Database connection
├── includes/
│   ├── auth.php           # Auth helper functions
│   └── utils.php          # Utility functions
├── sql/
│   ├── schema.sql         # Database schema
│   ├── sample_data.sql    # Sample recipes and data
│   └── admin_panel_upgrade.sql # Admin users + ratings tables
├── index.php              # Home page
├── recipe.php             # Recipe detail page
├── login.php              # Login page
├── register.php           # Registration page
└── README.md              # This file
```

## Installation & Setup

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Apache/Nginx web server
- mod_rewrite enabled (for clean URLs)

### Step-by-Step Setup

1. **Clone or extract the project**
   ```bash
   cd /path/to/your/webroot
   # Place all files in mood-food-recommender directory
   ```

2. **Create Database**
   ```bash
   mysql -u root -p
   ```
   ```sql
   CREATE DATABASE mood_food_recommender;
   EXIT;
   ```

3. **Import Database Schema**
   ```bash
   # Default MySQL port
   mysql -u root -p mood_food_recommender < sql/schema.sql

   # If your MySQL runs on a custom port (e.g. 3307 on XAMPP):
   # mysql -u root -p -P 3307 mood_food_recommender < sql/schema.sql
   ```

4. **Import Sample Data**
   ```bash
   mysql -u root -p mood_food_recommender < sql/sample_data.sql

   # Or, with custom port:
   # mysql -u root -p -P 3307 mood_food_recommender < sql/sample_data.sql
   ```

5. **Configure Database Connection**
   Edit `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_PORT', 3307); // Use 3307 for XAMPP default in this project
   define('DB_NAME', 'mood_food_recommender');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

6. **Configure Base URL**
   Edit `config/config.php`:
   ```php
   define('BASE_URL', 'http://localhost/mood-food-recommender');
   ```

7. **Set Permissions** (Linux/Mac)
   ```bash
   chmod 755 -R .
   chmod 777 assets/images/  # If using local image uploads
   ```

8. **Start Web Server**
   - **XAMPP/WAMP**: Start Apache and MySQL from control panel
   - **Linux**: `sudo systemctl start apache2 mysql`
   - **Development**: `php -S localhost:8000` (from project root)

9. **Access Application**
   Open browser: `http://localhost/mood-food-recommender`

## Admin Panel

The project includes a professional admin panel for managing recipes, moods, users, and analytics.

### Configuration overview

- **Central config**: All global settings, sessions, and helpers are loaded via `config/config.php`.
- **Sessions**: Session ini settings are applied before `session_start()` in `config/config.php`, and sessions are started once globally.
- **PDO**: `config/database.php` provides a shared `getDB()` function using PDO, `utf8mb4`, and port `3307`.

### 1) Run the admin DB upgrade

Import the admin upgrade SQL (adds `admin_users` and `recipe_ratings` tables):

```bash
mysql -u root -p mood_food_recommender < sql/admin_panel_upgrade.sql
```

### 2) Open the admin login

Open:

- `http://localhost/mood-food-recommender/admin/login.php`

### 3) First-time admin setup (bootstrap)

If **no rows exist in `admin_users`**, the login page will automatically show a **“Create the first admin account”** form.
After creating it, log in and you’ll be redirected to the admin dashboard.

### 4) Security note

Admin pages are protected by session auth. If you’re not logged in, you’ll be redirected to `admin/login.php`.

### 5) Admin URLs & redirect troubleshooting

- **Base URL** is defined in `config/config.php`:
  ```php
  define('BASE_URL', 'http://localhost/mood-food-recommender');
  ```
- **Admin URLs** are built with the global `admin_url()` helper (defined in `config/config.php`), for example:
  ```php
  admin_url('dashboard.php'); // http://localhost/mood-food-recommender/admin/dashboard.php
  ```
- **Redirects** use the global `redirect()` helper (also in `config/config.php`), which normalizes relative paths against `BASE_URL`. This ensures that even if code calls:
  ```php
  redirect('admin/dashboard.php');
  redirect('/admin/dashboard.php');
  ```
  the browser will always be sent to:
  ```text
  http://localhost/mood-food-recommender/admin/dashboard.php
  ```
- If you see a **“Not Found – The requested URL was not found on this server.”** after admin login, verify:
  - `BASE_URL` points to your project root (e.g. `http://localhost/mood-food-recommender`).
  - You’re visiting `http://localhost/mood-food-recommender/admin/login.php` (not `http://localhost/admin/login.php`).
  - Any custom redirects use `redirect('admin/...')` or `admin_url()` instead of hard-coded `/admin/...` URLs.

### Test Credentials

After importing sample data, you can login with:
- **Username**: `testuser` / **Password**: `test123`
- **Username**: `premiumuser` / **Password**: `test123` (Premium account)

### Available Moods

The system includes **9 moods** with unique recipe recommendations:
- 😊 **Happy**: Bright, colorful, celebratory foods
- 😰 **Stressed**: Comforting, warm, soothing dishes
- 😴 **Tired**: Energy-boosting, high-protein meals
- 🤔 **Bored**: Fun, creative fusion dishes
- ❤️ **Romantic**: Cozy, aesthetic meals for special moments
- 😡 **Angry**: Spicy, crunchy dishes to release tension
- 😌 **Relaxed**: Mild, warm, slow-cooked comfort foods
- 🌧️ **Rainy**: Cozy, warm comfort bowls
- ☀️ **Sunny**: Fresh, cool, refreshing bites

## API Endpoints

### Authentication
- `POST /api/auth.php?action=register` - Register new user
- `POST /api/auth.php?action=login` - Login user
- `POST /api/auth.php?action=logout` - Logout user
- `GET /api/auth.php?action=me` - Get current user

### Recipes
- `GET /api/moods.php` - List all moods
- `GET /api/suggestions.php?mood=<slug>&is_veg=<0|1>&is_high_protein=<0|1>` - Get recipe suggestions
- `GET /api/recipe.php?id=<recipe_id>` - Get recipe details

### Subscription
- `GET /api/subscription.php` - Get subscription status
- `POST /api/subscription.php?action=toggle` - Toggle premium (testing)

### Admin
- `GET /api/admin/recipes.php` - List all recipes
- `GET /api/admin/recipes.php?action=get&id=<id>` - Get single recipe
- `POST /api/admin/recipes.php?action=create` - Create recipe
- `POST /api/admin/recipes.php?action=update` - Update recipe
- `DELETE /api/admin/recipes.php?action=delete&id=<id>` - Delete recipe

## Sample API Responses

### Get Suggestions
```json
{
  "success": true,
  "data": {
    "suggestions": [
      {
        "id": 1,
        "title": "Colorful Mango Lassi",
        "slug": "colorful-mango-lassi",
        "cuisine": "Indian",
        "cuisine_emoji": "🇮🇳",
        "image_url": "https://images.unsplash.com/...",
        "is_veg": true,
        "is_high_protein": false,
        "calories": 180,
        "prep_time": 5,
        "cook_time": 0,
        "mood_tags": ["happy"],
        "score": 85.5
      }
    ],
    "count": 4,
    "mood": "happy",
    "filters_applied": {}
  }
}
```

### Get Recipe Detail
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Colorful Mango Lassi",
    "description": "A vibrant, sweet Nepali-inspired drink...",
    "cuisine": "Indian",
    "image_url": "https://images.unsplash.com/...",
    "is_veg": true,
    "is_high_protein": false,
    "nutrition": {
      "calories": 180,
      "proteins_g": 8.5,
      "carbs_g": 35.0,
      "fats_g": 2.5
    },
    "timing": {
      "prep_time": 5,
      "cook_time": 0,
      "servings": 2
    },
    "ingredients": [
      {"qty": "2", "unit": "cups", "item": "ripe mango, diced"}
    ],
    "steps": [
      {"step": 1, "instruction": "Blend mango, yogurt, and honey until smooth."}
    ],
    "mood_tags": ["happy"]
  }
}
```

## Recipe Suggestion Algorithm

The suggestion algorithm uses a weighted scoring system:

- **Mood Match (60%)**: Primary factor - recipes matching the selected mood get highest score
- **Filter Match (30%)**: Vegetarian/high-protein preferences boost score
- **Popularity (10%)**: Based on view count and random factor for variety

If fewer than 3-4 matches are found, the system returns the best available matches with a note.

## Image Handling

### Recommended Approach: CDN/External URLs

The sample data uses Unsplash placeholder URLs. For production:

1. **Use CDN** (Recommended):
   - Upload images to Cloudinary, AWS S3, or similar
   - Store full URLs in `image_url` field
   - Fast, scalable, no server storage needed

2. **Local Storage**:
   - Upload to `assets/images/recipes/`
   - Store relative paths: `assets/images/recipes/recipe-1.jpg`
   - Update `.htaccess` to allow image access

3. **Placeholder Service**:
   - Use Unsplash Source API: `https://source.unsplash.com/800x600/?food`
   - Or placeholder.com: `https://via.placeholder.com/800x600`

### Image Upload (Admin)

For admin image uploads, add to `api/admin/images.php`:
```php
// Handle file upload
$uploadDir = '../assets/images/recipes/';
$filename = uniqid() . '_' . basename($_FILES['image']['name']);
move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
```

## Deployment

### Production Checklist

1. **Security**
   - [ ] Change database credentials
   - [ ] Set `display_errors = 0` in `php.ini`
   - [ ] Enable HTTPS (set `session.cookie_secure = 1`)
   - [ ] Add CSRF protection for forms
   - [ ] Implement rate limiting
   - [ ] Add input validation on all endpoints

2. **Performance**
   - [ ] Enable PHP OPcache
   - [ ] Add database indexes (already in schema)
   - [ ] Implement caching (Redis/Memcached for suggestions)
   - [ ] Use CDN for images and static assets
   - [ ] Enable Gzip compression

3. **Database**
   - [ ] Create dedicated database user with limited privileges
   - [ ] Regular backups
   - [ ] Monitor query performance

4. **Server**
   - [ ] Update `BASE_URL` in `config/config.php`
   - [ ] Set proper file permissions
   - [ ] Configure `.htaccess` for security headers
   - [ ] Set up SSL certificate

### LAMP Stack Deployment

1. **Apache Configuration**
   ```apache
   <VirtualHost *:80>
       ServerName yourdomain.com
       DocumentRoot /var/www/mood-food-recommender
       
       <Directory /var/www/mood-food-recommender>
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

2. **PHP Configuration**
   - PHP 7.4+ with extensions: `pdo_mysql`, `json`, `mbstring`
   - `upload_max_filesize = 10M`
   - `post_max_size = 10M`

3. **MySQL Configuration**
   - Create database and user
   - Import schema and data
   - Set `max_connections` appropriately

## Testing Checklist

### Functionality
- [ ] Mood selection works
- [ ] Suggestions load correctly (3-4 recipes)
- [ ] Filters (veg/high-protein) work
- [ ] Recipe detail page displays all information
- [ ] User registration works
- [ ] User login/logout works
- [ ] Premium features gated correctly

### Responsiveness
- [ ] Mobile (320px, 375px, 414px)
- [ ] Tablet (768px, 1024px)
- [ ] Desktop (1280px, 1920px)
- [ ] Touch interactions work
- [ ] Text is readable at all sizes

### Accessibility
- [ ] Keyboard navigation works
- [ ] Screen reader compatible (ARIA labels)
- [ ] Color contrast meets WCAG AA
- [ ] Focus indicators visible
- [ ] Forms have proper labels

### Security
- [ ] SQL injection prevention (prepared statements)
- [ ] XSS prevention (input sanitization)
- [ ] CSRF protection (add tokens)
- [ ] Password hashing (bcrypt)
- [ ] Session security (httponly, secure cookies)

### Performance
- [ ] Page load < 3 seconds
- [ ] API responses < 500ms
- [ ] Images optimized/lazy loaded
- [ ] No console errors

## Premium Features

The subscription system supports:
- **Unlimited Saved Recipes**: Free users limited to 10 saved recipes
- **Custom Meal Plans**: Premium-only feature
- **Ad-Free Experience**: No ads for premium users
- **Priority Support**: Faster response times

To toggle premium for testing:
```bash
POST /api/subscription.php?action=toggle
```

## UX Microcopy

### Mood Selection
- **Happy**: "Celebrate with bright, colorful, and uplifting foods! ✨"
- **Stressed**: "Comfort foods to soothe and relax your mind. 🧘"
- **Tired**: "Energy-boosting meals to recharge and revitalize. ⚡"

### Buttons
- "Find My Recipes" (primary CTA)
- "View Recipe" (recipe cards)
- "Save Recipe" (premium feature)
- "Clear Filters" (filter section)

### Empty States
- "No recipes found. Try adjusting your filters! 🍽️"
- "Select a mood to discover recipes! 😊"
- "Couldn't find recipes. Please try again! 😔"

### Loading States
- "Finding perfect recipes for you... ✨"
- "Loading recipe... 🍳"
- "Saving your preferences... 💾"

## Troubleshooting

### Database Connection Error
- Check database credentials in `config/database.php`
- Verify MySQL service is running
- Ensure database exists

### API Returns 500 Error
- Check PHP error logs
- Verify database connection
- Check file permissions

### Images Not Loading
- Verify image URLs are accessible
- Check CORS if using external CDN
- Ensure `assets/images/` directory exists and is writable

### Session Issues
- Check `session.save_path` in `php.ini`
- Verify cookies are enabled
- Check `session.cookie_httponly` setting

## Contributing

This is a production-ready template. To extend:

1. Add more cuisines in `cuisines` table
2. Add more moods in `moods` table
3. Implement recipe rating system
4. Add meal planning feature
5. Implement search functionality
6. Add recipe sharing/social features

## License

This project is provided as-is for educational and commercial use.

## Support

For issues or questions:
1. Check this README
2. Review code comments
3. Check PHP error logs
4. Verify database schema matches sample data

---

**Made with ❤️ and 🍽️** - Enjoy your mood-based recipe recommendations!

