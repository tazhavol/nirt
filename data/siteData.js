/**
 * NIRT Data Adapter
 * اتصال Frontend به Backend API
 * در صورت عدم دسترسی به API، از داده‌های پیش‌فرض استفاده می‌کند
 */

const API_BASE = 'api/'; // مسیر نسبی به پوشه api

const DataAdapter = {
    // کش موقت برای بهینه‌سازی
    cache: {},
    
    /**
     * دریافت اطلاعات پلیر
     */
    async getPlayer() {
        try {
            const response = await fetch(`${API_BASE}player.php`);
            const result = await response.json();
            if (result.success && result.data) {
                return result.data;
            }
        } catch (e) {
            console.warn('API Error (Player), using fallback');
        }
        return {
            title: "پخش زنده شبکه یک سیما",
            video: "assets/videos/live-stream.mp4",
            thumbnail: "assets/images/live-thumb.jpg"
        };
    },

    /**
     * دریافت لیست برنامه‌ها
     */
    async getPrograms() {
        try {
            const response = await fetch(`${API_BASE}programs.php`);
            const result = await response.json();
            if (result.success && result.data) {
                return result.data;
            }
        } catch (e) {
            console.warn('API Error (Programs), using fallback');
        }
        return [
            {id: 1, title: "اخبار سراسری", image: "assets/images/news.jpg", description: "آخرین اخبار"},
            {id: 2, title: "نمایش خانگی", image: "assets/images/drama.jpg", description: "سریال"}
        ];
    },

    /**
     * دریافت شبکه‌های اجتماعی
     */
    async getSocial() {
        try {
            const response = await fetch(`${API_BASE}social.php`);
            const result = await response.json();
            if (result.success && result.data) {
                return result.data;
            }
        } catch (e) {
            console.warn('API Error (Social), using fallback');
        }
        return [
            {platform: "Telegram", url: "#", icon: "telegram"},
            {platform: "Instagram", url: "#", icon: "instagram"}
        ];
    },

    /**
     * دریافت اطلاعات تماس
     */
    async getContact() {
        try {
            const response = await fetch(`${API_BASE}contact.php`);
            const result = await response.json();
            if (result.success && result.data) {
                return result.data;
            }
        } catch (e) {
            console.warn('API Error (Contact), using fallback');
        }
        return {
            phone: "021-2789-1234",
            email: "info@nirt.ir",
            address: "تهران، خیابان ولیعصر"

        };
    },

    /**
     * دریافت تنظیمات تبلیغات
     */
    async getAds() {
        try {
            const response = await fetch(`${API_BASE}ads.php`);
            const result = await response.json();
            if (result.success && result.data) {
                return result.data;
            }
        } catch (e) {
            console.warn('API Error (Ads), using fallback');
        }
        return {
            title: "همکاری تبلیغاتی",
            description: "با ما در ارتباط باشید"
        };
    },

    /**
     * ارسال فرم تماس
     */
    async submitContact(formData) {
        try {
            const response = await fetch(`${API_BASE}contact.php`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(formData)
            });
            return await response.json();
        } catch (e) {
            return {success: false, message: 'Connection error'};
        }
    },

    /**
     * ارسال درخواست تبلیغات
     */
    async submitAds(formData) {
        try {
            const response = await fetch(`${API_BASE}ads.php`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(formData)
            });
            return await response.json();
        } catch (e) {
            return {success: false, message: 'Connection error'};
        }
    }
};

/**
 * نسخه سازگار با کد قبلی app.js
 * برای جلوگیری از شکستن کد قبلی، یک Promise wrapper ارائه می‌دهیم
 */

let siteDataCache = null;

async function loadSiteData() {
    if (siteDataCache) return siteDataCache;
    
    const [player, programs, social, contact, ads] = await Promise.all([
        DataAdapter.getPlayer(),
        DataAdapter.getPrograms(),
        DataAdapter.getSocial(),
        DataAdapter.getContact(),
        DataAdapter.getAds()
    ]);
    
    siteDataCache = {
        player,
        programs,
        social,
        contact,
        ads
    };
    
    return siteDataCache;
}

// برای سازگاری با کد قبلی که synchronous بود
// صبر می‌کنیم تا داده‌ها لود شوند سپس app.js اجرا می‌شود
window.siteDataPromise = loadSiteData();
