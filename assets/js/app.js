/**
 * NIRT Application Core
 * Architecture: Data-Driven Static Frontend powered by Cloudflare D1 & Workers
 * No Framework - Vanilla JavaScript Only
 */

class NIRTApp {
  constructor() {
    this.currentPage = 'home';
    this.data = {
      player: null,
      social: [],
      programs: [],
      contact: null,
      ads: null
    };
    this.playerState = {
      isPlaying: false,
      isMuted: false,
      volume: 0.8,
      currentTime: 0,
      duration: 0
    };
    
    // تعریف آدرس اصلی API ورکر شما
    this.apiUrl = window.API_URL || "https://nirtdb.mansour-m.workers.dev";
    
    this.init();
  }

  async init() {
    this.cacheDOM();
    this.bindEvents();
    
    // دریافت اطلاعات زنده از دیتابیس D1 کلودفلر
    await this.loadAllDataFromD1();
    
    this.renderData();
    this.initVideoPlayer();
    this.navigate('home');
  }

  // متد کمکی برای خواندن از جداول دیتابیس
  async fetchTable(tableName) {
    try {
      const response = await fetch(`${this.apiUrl}?table=${tableName}`);
      if (!response.ok) throw new Error(`خطا در دریافت جدول ${tableName}`);
      return await response.json();
    } catch (error) {
      console.error(`خطا در ارتباط با دیتابیس برای جدول ${tableName}:`, error);
      return null;
    }
  }

  // دریافت تمام داده‌های دیتابیس به صورت همزمان
  async loadAllDataFromD1() {
    try {
      // صدا زدن تمام جدول‌ها به صورت همزمان برای افزایش سرعت لود سایت
      const [playerData, socialData, programsData, contactData, adsData] = await Promise.all([
        this.fetchTable('player_settings'),
        this.fetchTable('social_links'),
        this.fetchTable('programs'),
        this.fetchTable('contact_settings'),
        this.fetchTable('ads_settings')
      ]);

      // فرآوری داده‌های جدول player_settings (گرفتن اولین رکورد)
      if (playerData && playerData.length > 0) {
        this.data.player = {
          title: playerData[0].title || "پخش زنده",
          thumbnail: playerData[0].thumbnail || "assets/images/live-thumb.jpg",
          video: playerData[0].video || "assets/videos/live-stream.mp4"
        };
      }

      // فرآوری داده‌های جدول social_links
      if (socialData) {
        this.data.social = socialData;
      }

      // فرآوری داده‌های جدول programs
      if (programsData) {
        this.data.programs = programsData;
      }

      // فرآوری داده‌های جدول contact_settings (گرفتن اولین رکورد)
      if (contactData && contactData.length > 0) {
        this.data.contact = contactData[0];
      }

      // فرآوری داده‌های جدول ads_settings (گرفتن اولین رکورد)
      if (adsData && adsData.length > 0) {
        this.data.ads = {
          title: adsData[0].title || "همکاری تبلیغاتی",
          description: adsData[0].description || "متن پیش فرض..."
        };
      }

    } catch (error) {
      console.error("خطا در لود اطلاعات اولیه دیتابیس:", error);
    }
  }

  cacheDOM() {
    this.navItems = document.querySelectorAll('.nav-item');
    this.mobileNavBtns = document.querySelectorAll('.mobile-nav-btn');
    this.hamburgerBtn = document.getElementById('hamburgerBtn');
    this.closeMenuBtn = document.getElementById('closeMenu');
    this.mobileMenu = document.getElementById('mobileMenu');
    
    this.pages = document.querySelectorAll('.page');
    this.video = document.getElementById('mainPlayer');
    this.playPauseBtn = document.getElementById('playPauseBtn');
    this.progressBar = document.getElementById('progressBar');
    this.progressFill = document.getElementById('progressFill');
    this.progressHandle = document.getElementById('progressHandle');
    this.timeDisplay = document.getElementById('timeDisplay');
    this.volumeSlider = document.getElementById('volumeSlider');
    this.fullscreenBtn = document.getElementById('fullscreenBtn');
    this.videoOverlay = document.getElementById('videoOverlay');
    
    this.socialGrid = document.getElementById('socialGrid');
    this.programsGrid = document.getElementById('programsGrid');
    this.contactDetails = document.getElementById('contactDetails');
    this.adsTitle = document.getElementById('adsTitle');
    this.adsDescription = document.getElementById('adsDescription');
    this.playerTitle = document.getElementById('playerTitle');
  }

  bindEvents() {
    this.navItems.forEach(item => {
      item.addEventListener('click', (e) => {
        const page = item.dataset.page;
        this.navigate(page);
      });
    });

    this.mobileNavBtns.forEach(btn => {
      btn.addEventListener('click', (e) => {
        const page = btn.dataset.page;
        this.navigate(page);
        this.closeMobileMenu();
      });
    });

    this.hamburgerBtn?.addEventListener('click', () => this.openMobileMenu());
    this.closeMenuBtn?.addEventListener('click', () => this.closeMobileMenu());

    document.getElementById('adsForm')?.addEventListener('submit', (e) => {
      e.preventDefault();
      this.handleFormSubmit(e.target, 'ads_requests'); // ثبت در جدول درخواست‌های تبلیغات
    });

    document.getElementById('contactForm')?.addEventListener('submit', (e) => {
      e.preventDefault();
      this.handleFormSubmit(e.target, 'contact_messages'); // ثبت در جدول پیام‌های تماس
    });
  }

  navigate(page) {
    this.currentPage = page;
    
    this.pages.forEach(p => p.classList.remove('active'));
    document
      .getElementById(`page${page.charAt(0).toUpperCase() + page.slice(1)}`)?.classList.add('active');
    this.navItems.forEach(item => {
      item.classList.toggle('active', item.dataset.page === page);
    });

    this.mobileNavBtns.forEach(btn => {
      btn.classList.toggle('active', btn.dataset.page === page);
    });
  }

  openMobileMenu() {
    this.mobileMenu.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  closeMobileMenu() {
    this.mobileMenu.classList.remove('active');
    document.body.style.overflow = '';
  }

  renderData() {
    // رندر بخش پخش زنده
    if (this.data.player && this.playerTitle) {
      this.playerTitle.textContent = this.data.player.title;
      this.video.poster = this.data.player.thumbnail;
      this.video.src = this.data.player.video;
    }

    // رندر بخش شبکه‌های اجتماعی
    if (this.data.social && this.socialGrid) {
      this.socialGrid.innerHTML = this.data.social.map(social => `
        <a href="${social.url}" target="_blank" class="social-btn" aria-label="${social.platform}">
          ${this.getSocialIcon(social.platform)}
        </a>
      `).join('');
    }

    // رندر لیست برنامه‌ها
    if (this.data.programs && this.programsGrid) {
      this.programsGrid.innerHTML = this.data.programs.map(program => `
        <div class="program-card">
          <img src="${program.image}" alt="${program.title}" class="program-image">
          <div class="program-info">
            <h3 class="program-title">${program.title}</h3>
            <p class="program-desc">${program.description}</p>
          </div>
        </div>
      `).join('');
    }

    // نمایش اطلاعات تماس
    if (this.data.contact) {
      const { phone, email, address, map_image } = this.data.contact;
      
      if (this.contactDetails) {
        this.contactDetails.innerHTML = `
          <div class="contact-item">
            <span class="label">تلفن:</span>
            <span class="value">${phone || '-'}</span>
          </div>
          <div class="contact-item">
            <span class="label">ایمیل:</span>
            <span class="value">${email || '-'}</span>
          </div>
          <div class="contact-item">
            <span class="label">آدرس:</span>
            <span class="value">${address || '-'}</span>
          </div>
        `;
      }

      // نمایش نقشه — فقط تصویر، بدون Google Maps
      const mapFrame = document.getElementById('siteMapFrame');
      const mapImg = document.getElementById('siteMapImg');

      if (mapImg && map_image) {
        if (mapFrame) mapFrame.style.display = 'none';
        mapImg.src = `uploads/${map_image}`;
        mapImg.style.display = 'block';
      } else {
        if (mapFrame) mapFrame.style.display = 'none';
        if (mapImg) mapImg.style.display = 'none';
      }
    }

    // رندر متن بخش تبلیغات
    if (this.data.ads && this.adsTitle && this.adsDescription) {
      this.adsTitle.textContent = this.data.ads.title;
      this.adsDescription.textContent = this.data.ads.description;
    }
  }

  getSocialIcon(platform) {
    const icons = {
      Telegram: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>',
      Instagram: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>',
      YouTube: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
      Twitter: '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>'
    };
    return icons[platform] || icons.Telegram;
  }

  initVideoPlayer() {
    if (!this.video) return;

    this.playPauseBtn?.addEventListener('click', () => this.togglePlay());
    this.videoOverlay?.addEventListener('click', () => this.togglePlay());
    this.video?.addEventListener('click', () => this.togglePlay());

    this.video?.addEventListener('timeupdate', () => this.updateProgress());
    this.video?.addEventListener('loadedmetadata', () => {
      this.playerState.duration = this.video.duration;
    });
    
    this.progressBar?.addEventListener('click', (e) => {
      const rect = this.progressBar.getBoundingClientRect();
      const pos = (e.clientX - rect.left) / rect.width;
      this.video.currentTime = pos * this.video.duration;
    });

    this.volumeSlider?.addEventListener('input', (e) => {
      this.video.volume = e.target.value / 100;
      this.playerState.volume = e.target.value / 100;
    });

    if (this.volumeSlider) {
      this.volumeSlider.value = 80;
    }

    this.fullscreenBtn?.addEventListener('click', () => {
      if (!document.fullscreenElement) {
        this.video.requestFullscreen?.();
      } else {
        document.exitFullscreen?.();
      }
    });
  }

  togglePlay() {
    if (!this.video) return;
    if (this.video.paused) {
      this.video.play();
      this.playerState.isPlaying = true;
      this.videoOverlay?.classList.add('hidden');
      this.playPauseBtn?.classList.add('playing');
    } else {
      this.video.pause();
      this.playerState.isPlaying = false;
      this.videoOverlay?.classList.remove('hidden');
      this.playPauseBtn?.classList.remove('playing');
    }
  }

  updateProgress() {
    if (!this.video || !this.progressFill || !this.timeDisplay) return;

    const current = this.video.currentTime;
    const duration = this.video.duration || 0;
    const percent = duration ? (current / duration) * 100 : 0;

    this.progressFill.style.width = `${percent}%`;
    this.timeDisplay.textContent = `${this.formatTime(current)} / ${this.formatTime(duration)}`;
  }

  formatTime(seconds) {
    if (!isFinite(seconds)) return '00:00';
    const m = Math.floor(seconds / 60).toString().padStart(2, '0');
    const s = Math.floor(seconds % 60).toString().padStart(2, '0');
    return `${m}:${s}`;
  }

  // مدیریت ارسال مستقیم فرم‌ها به جدول‌های دیتابیس D1 کلودفلر
  async handleFormSubmit(form, tableName) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const span = submitBtn.querySelector('span') || submitBtn;
    const originalText = span.textContent;

    submitBtn.disabled = true;
    span.textContent = 'در حال ارسال...';

    const payload = Object.fromEntries(new FormData(form));

    try {
      // ارسال داده به آدرس ورکر کلودفلر به جای اسکریپت PHP قبلی
      const response = await fetch(this.apiUrl, {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json' 
        },
        body: JSON.stringify({
          table: tableName,
          data: payload
        })
      });

      const resData = await response.json();

      if (!response.ok || resData.error) {
        throw new Error(resData.error || 'خطا در ثبت اطلاعات در دیتابیس');
      }

      submitBtn.classList.add('btn-success');
      span.textContent = '✓ ارسال شد';
      this.showToast(`اطلاعات شما با موفقیت ثبت شد`, 'success');
      form.reset();

    } catch (error) {
      console.error("خطای ارسال فرم:", error);
      submitBtn.classList.add('btn-error');
      span.textContent = '✗ خطا در ارسال';
      this.showToast(error.message || 'خطا در برقراری ارتباط با دیتابیس', 'error');

    } finally {
      setTimeout(() => {
        submitBtn.disabled = false;
        submitBtn.classList.remove('btn-success', 'btn-error');
        span.textContent = originalText;
      }, 3000);
    }
  }

  showToast(message, type = 'info') {
    let toast = document.querySelector('.toast');
    if (!toast) {
      toast = document.createElement('div');
      toast.className = 'toast';
      document.body.appendChild(toast);
    }

    toast.textContent = message;
    toast.className = `toast toast-${type}`;

    setTimeout(() => {
      toast.className = 'toast';
    }, 4000);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  window.nirtApp = new NIRTApp();
});

function switchToAparat(url) {
  document.getElementById('mainPlayer').style.display = 'none';
  document.getElementById('videoControls').style.display = 'none';
  document.getElementById('aparatIframe').src = url;
  document.getElementById('aparatPlayer').style.display = 'block';
}

function switchToLocal() {
  document.getElementById('mainPlayer').style.display = 'block';
  document.getElementById('videoControls').style.display = 'flex';
  document.getElementById('aparatIframe').src = '';
  document.getElementById('aparatPlayer').style.display = 'none';
}
