<?php

class WidgetController {

    // Returns the JS file dynamically
    public function serveScript() {
        header('Content-Type: application/javascript');

        $widget_id = (int)($_GET['w'] ?? 0);

        // Construct the base URL for API calls
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $baseUrl = $protocol . $host . '/api';

        echo <<<JS
(function() {
    const WIDGET_ID = '$widget_id';
    const API_BASE = '$baseUrl';

    // Configuration
    const DISPLAY_DURATION_MS = 5000;
    const HIDE_DURATION_MS = 5000;
    const CONTAINER_ID = 'trustabee-widget';
    const COUPON_CONTAINER_ID = 'trustabee-coupon-modal';
    const ANNOUNCEMENT_CONTAINER_ID = 'trustabee-announcement-modal';
    const VIDEO_CONTAINER_ID = 'trustabee-video-modal';
    const NEWSLETTER_CONTAINER_ID = 'trustabee-newsletter-modal';
    const SOCIAL_CONTAINER_ID = 'trustabee-social-widget';

    let data = [];
    let coupons = [];
    let announcements = [];
    let videos = [];
    let newsletters = [];
    let socials = [];
    let currentIndex = 0;
    let widgetElement;
    let timerId;

    // --- API Calls ---

    async function fetchData() {
        try {
            const response = await fetch(`\${API_BASE}/data?w=\${WIDGET_ID}`);
            const json = await response.json();

            // Store coupons
            if (json.coupons && json.coupons.length > 0) {
                coupons = json.coupons;
                checkCoupons();
            }

            // Store announcements
            if (json.announcements && json.announcements.length > 0) {
                announcements = json.announcements;
                checkAnnouncements();
            }

            // Store videos
            if (json.videos && json.videos.length > 0) {
                videos = json.videos;
                checkVideos();
            }

            // Store newsletters
            if (json.newsletters && json.newsletters.length > 0) {
                newsletters = json.newsletters;
                checkNewsletters();
            }

            // Store socials
            if (json.socials && json.socials.length > 0) {
                socials = json.socials;
                checkSocials();
            }

            // Build queue
            data = [];

            // 1. Live count
            // Only if enabled in config
            const isLiveEnabled = json.config && json.config.live_visitor_enabled;
            if (isLiveEnabled && json.live_count > 0) {
                 data.push({
                    type: 'live_count',
                    count: json.live_count,
                    text: `\${json.live_count} people are viewing this page right now.`
                 });
            }

            // 2. Historical
            if (json.historical_count > 0) {
                data.push({
                    type: 'historical',
                    count: json.historical_count,
                    text: `\${json.historical_count} people signed up in the last 7 days.`
                });
            }

            // 3. Notifications (Real + Simulated)
            if (json.notifications && json.notifications.length > 0) {
                data = data.concat(json.notifications);
            }

            if (data.length > 0) {
                initWidget(json.config ? json.config.live_visitor_config : null);
            }

            if (json.config && json.config.magical_detection) {
                enableMagicalDetection();
            }
        } catch (e) {
            console.error('Trustabee: Error fetching data', e);
        }
    }

    function sendHeartbeat() {
        let visitorId = localStorage.getItem('trustabee_vid');
        if (!visitorId) {
            visitorId = 'v_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('trustabee_vid', visitorId);
        }

        const payload = new URLSearchParams();
        payload.append('w', WIDGET_ID);
        payload.append('vid', visitorId);
        payload.append('url', window.location.href);

        if (navigator.sendBeacon) {
            navigator.sendBeacon(`\${API_BASE}/heartbeat`, payload);
        } else {
            fetch(`\${API_BASE}/heartbeat`, { method: 'POST', body: payload });
        }
    }

    function trackConversion(formData) {
        let visitorId = localStorage.getItem('trustabee_vid');
        const payload = new URLSearchParams();
        payload.append('w', WIDGET_ID);
        payload.append('vid', visitorId);
        payload.append('type', 'form_submit');
        payload.append('page', window.location.href);

        let formObj = {};
        formData.forEach((value, key) => { formObj[key] = value });
        payload.append('payload', JSON.stringify(formObj));

         if (navigator.sendBeacon) {
            navigator.sendBeacon(`\${API_BASE}/track`, payload);
        } else {
            fetch(`\${API_BASE}/track`, { method: 'POST', body: payload });
        }
    }

    function trackCouponEvent(couponId, eventType) {
        const payload = new URLSearchParams();
        payload.append('w', WIDGET_ID);
        payload.append('cid', couponId);
        payload.append('type', eventType);

        if (navigator.sendBeacon) {
            navigator.sendBeacon(`\${API_BASE}/track-coupon`, payload);
        } else {
            fetch(`\${API_BASE}/track-coupon`, { method: 'POST', body: payload });
        }
    }

    function trackAnnouncementEvent(announcementId, eventType) {
        const payload = new URLSearchParams();
        payload.append('w', WIDGET_ID);
        payload.append('aid', announcementId);
        payload.append('type', eventType);

        if (navigator.sendBeacon) {
            navigator.sendBeacon(`\${API_BASE}/track-announcement`, payload);
        } else {
            fetch(`\${API_BASE}/track-announcement`, { method: 'POST', body: payload });
        }
    }

    function trackVideoEvent(videoId, eventType) {
        const payload = new URLSearchParams();
        payload.append('w', WIDGET_ID);
        payload.append('vid', videoId);
        payload.append('type', eventType);

        if (navigator.sendBeacon) {
            navigator.sendBeacon(`\${API_BASE}/track-video`, payload);
        } else {
            fetch(`\${API_BASE}/track-video`, { method: 'POST', body: payload });
        }
    }

    function trackSocialEvent(socialId, eventType, linkId = null) {
        const payload = new URLSearchParams();
        payload.append('w', WIDGET_ID);
        payload.append('sid', socialId);
        payload.append('type', eventType);
        if (linkId) payload.append('lid', linkId);

        if (navigator.sendBeacon) {
            navigator.sendBeacon(`\${API_BASE}/track-social`, payload);
        } else {
            fetch(`\${API_BASE}/track-social`, { method: 'POST', body: payload });
        }
    }

    // --- Shared Logic ---

    function setupTrigger(item, showCallback) {
        if (item.trigger_type === 'exit_intent') {
            const handler = (e) => {
                if (e.clientY <= 0) {
                    document.removeEventListener('mouseleave', handler);
                    showCallback(item);
                }
            };
            document.addEventListener('mouseleave', handler);
        } else {
            // Default to delay
            setTimeout(() => showCallback(item), (item.trigger_delay || 0) * 1000);
        }
    }

    function shouldShowItem(item, type) {
        // 1. URL Match
        if (item.match_url && item.match_url.trim() !== '') {
            if (!window.location.href.includes(item.match_url)) {
                return false;
            }
        }

        // 2. Frequency
        const storageKey = `trustabee_\${type}_shown_\${item.id}`;
        const lastShown = localStorage.getItem(storageKey);

        if (item.frequency === 'session') {
            // Check if shown in last 30 mins
            if (lastShown) {
                const now = new Date().getTime();
                const diffMinutes = (now - parseInt(lastShown)) / 1000 / 60;
                if (diffMinutes < 30) return false;
            }
        }
        // 'every_load' just passes through

        return true;
    }

    // --- Coupon Logic ---

    function checkCoupons() {
        for (const coupon of coupons) {
            if (shouldShowItem(coupon, 'coupon')) {
                setupTrigger(coupon, showCoupon);
                return; // Only show one coupon per page load
            }
        }
    }

    function showCoupon(coupon) {
        // Frequency check & set storage
        const storageKey = `trustabee_coupon_shown_\${coupon.id}`;
        localStorage.setItem(storageKey, new Date().getTime());

        if (document.getElementById(COUPON_CONTAINER_ID)) return;

        createModal(COUPON_CONTAINER_ID, coupon, (content) => {
            // Coupon Code Box
            const codeBox = document.createElement('div');
            codeBox.style.cssText = `
                border: 2px dashed #ccc; padding: 15px;
                margin: 0 0 20px 0; border-radius: 6px;
                font-size: 20px; font-weight: bold;
                background: rgba(0,0,0,0.03); letter-spacing: 1px;
            `;
            codeBox.textContent = coupon.coupon_code;
            content.appendChild(codeBox);

            // Button
            const btn = document.createElement('button');
            btn.textContent = coupon.button_text || 'Copy Code';
            btn.style.cssText = `
                background: #1a73e8; color: white; border: none;
                padding: 12px 24px; font-size: 16px; border-radius: 6px;
                cursor: pointer; width: 100%; font-weight: 600;
            `;
            btn.onclick = () => {
                navigator.clipboard.writeText(coupon.coupon_code).then(() => {
                    const originalText = btn.textContent;
                    btn.textContent = 'Copied!';
                    trackCouponEvent(coupon.id, 'click');
                    setTimeout(() => btn.textContent = originalText, 2000);
                });
            };
            content.appendChild(btn);
        });

        trackCouponEvent(coupon.id, 'view');
    }


    // --- Announcement Logic ---

    function checkAnnouncements() {
        for (const announcement of announcements) {
             // Avoid showing if coupon is already showing?
             // For now assume they can stack or overlap, but typically one modal at a time is best.
             // We'll let them overlap if configured so.
            if (shouldShowItem(announcement, 'announcement')) {
                setupTrigger(announcement, showAnnouncement);
                return;
            }
        }
    }

    function showAnnouncement(announcement) {
        const storageKey = `trustabee_announcement_shown_\${announcement.id}`;
        localStorage.setItem(storageKey, new Date().getTime());

        if (document.getElementById(ANNOUNCEMENT_CONTAINER_ID)) return;

        createModal(ANNOUNCEMENT_CONTAINER_ID, announcement, (content) => {
            // Button
            const btn = document.createElement('button');
            btn.textContent = announcement.btn_text || 'Learn More';
            btn.style.cssText = `
                background: #1a73e8; color: white; border: none;
                padding: 12px 24px; font-size: 16px; border-radius: 6px;
                cursor: pointer; width: 100%; font-weight: 600;
            `;

            btn.onclick = () => {
                trackAnnouncementEvent(announcement.id, 'click');
                if (announcement.btn_action === 'close') {
                     const modal = document.getElementById(ANNOUNCEMENT_CONTAINER_ID);
                     if (modal) {
                         modal.style.opacity = '0';
                         setTimeout(() => modal.remove(), 300);
                     }
                } else {
                    // Link
                    if (announcement.btn_link) {
                         window.location.href = announcement.btn_link;
                    }
                }
            };
            content.appendChild(btn);
        });

        trackAnnouncementEvent(announcement.id, 'view');
    }

    // --- Video Logic ---

    function checkVideos() {
        for (const video of videos) {
            if (shouldShowItem(video, 'video')) {
                setupTrigger(video, showVideo);
                return;
            }
        }
    }

    function createVideoElement(url) {
        if (url.includes('youtube.com') || url.includes('youtu.be')) {
            let videoId = '';
            if (url.includes('youtu.be')) {
                videoId = url.split('/').pop();
            } else {
                const params = new URLSearchParams(new URL(url).search);
                videoId = params.get('v');
            }
            if (videoId) {
                const iframe = document.createElement('iframe');
                iframe.width = "100%";
                iframe.height = "220";
                iframe.src = `https://www.youtube.com/embed/\${videoId}?autoplay=1&mute=1`;
                iframe.frameBorder = "0";
                iframe.allow = "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture";
                iframe.allowFullscreen = true;
                iframe.style.borderRadius = "8px";
                iframe.style.marginBottom = "20px";
                return iframe;
            }
        } else if (url.includes('vimeo.com')) {
            const videoId = url.split('/').pop();
            if (videoId) {
                const iframe = document.createElement('iframe');
                iframe.src = `https://player.vimeo.com/video/\${videoId}?autoplay=1&muted=1`;
                iframe.width = "100%";
                iframe.height = "220";
                iframe.frameBorder = "0";
                iframe.allow = "autoplay; fullscreen";
                iframe.allowFullscreen = true;
                iframe.style.borderRadius = "8px";
                iframe.style.marginBottom = "20px";
                return iframe;
            }
        } else {
            // Hosted video
            const videoEl = document.createElement('video');
            videoEl.src = url;
            videoEl.width = "100%"; // Note: width is attribute here, or style
            videoEl.style.width = "100%";
            videoEl.height = 220; // attribute
            videoEl.autoplay = true;
            videoEl.muted = true;
            videoEl.controls = true;
            videoEl.playsInline = true;
            videoEl.style.borderRadius = "8px";
            videoEl.style.marginBottom = "20px";
            videoEl.style.objectFit = "cover";
            return videoEl;
        }
        return null;
    }

    function showVideo(video) {
        const storageKey = `trustabee_video_shown_\${video.id}`;
        localStorage.setItem(storageKey, new Date().getTime());

        if (document.getElementById(VIDEO_CONTAINER_ID)) return;

        createModal(VIDEO_CONTAINER_ID, video, (content) => {
            // Video Embed
            if (video.video_url) {
                const videoEl = createVideoElement(video.video_url);
                if (videoEl) {
                    content.appendChild(videoEl);
                }
            }

            // Button
            const btn = document.createElement('button');
            btn.textContent = video.btn_text || 'Learn More';
            btn.style.cssText = `
                background: #1a73e8; color: white; border: none;
                padding: 12px 24px; font-size: 16px; border-radius: 6px;
                cursor: pointer; width: 100%; font-weight: 600;
            `;

            btn.onclick = () => {
                trackVideoEvent(video.id, 'click');
                if (video.btn_action === 'close') {
                     const modal = document.getElementById(VIDEO_CONTAINER_ID);
                     if (modal) {
                         modal.style.opacity = '0';
                         setTimeout(() => modal.remove(), 300);
                     }
                } else {
                    // Link
                    if (video.btn_link) {
                         window.location.href = video.btn_link;
                    }
                }
            };
            content.appendChild(btn);
        });

        trackVideoEvent(video.id, 'view');
    }

    // --- Newsletter Logic ---

    function checkNewsletters() {
        for (const newsletter of newsletters) {
            if (shouldShowItem(newsletter, 'newsletter')) {
                setupTrigger(newsletter, showNewsletter);
                return;
            }
        }
    }

    function showNewsletter(newsletter) {
        const storageKey = `trustabee_newsletter_shown_\${newsletter.id}`;
        localStorage.setItem(storageKey, new Date().getTime());

        if (document.getElementById(NEWSLETTER_CONTAINER_ID)) return;

        // Track View
        const payload = new URLSearchParams();
        payload.append('w', WIDGET_ID);
        payload.append('nid', newsletter.id);
        payload.append('type', 'view');
        if (navigator.sendBeacon) {
            navigator.sendBeacon(`\${API_BASE}/track-newsletter`, payload);
        } else {
            fetch(`\${API_BASE}/track-newsletter`, { method: 'POST', body: payload });
        }

        createModal(NEWSLETTER_CONTAINER_ID, newsletter, (content) => {
            // Form Container
            const form = document.createElement('form');
            form.style.marginTop = '15px';

            // Name Field
            if (newsletter.allow_name == 1) {
                const nameInput = document.createElement('input');
                nameInput.type = 'text';
                nameInput.name = 'name';
                nameInput.placeholder = 'Enter your full name...';
                nameInput.required = true;
                nameInput.style.cssText = 'width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;';
                form.appendChild(nameInput);
            }

            // Email Field
            const emailInput = document.createElement('input');
            emailInput.type = 'email';
            emailInput.name = 'email';
            emailInput.placeholder = 'Enter your email address...';
            emailInput.required = true;
            emailInput.style.cssText = 'width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;';
            form.appendChild(emailInput);

            // Phone Field
            if (newsletter.allow_phone == 1) {
                const phoneInput = document.createElement('input');
                phoneInput.type = 'tel';
                phoneInput.name = 'phone';
                phoneInput.placeholder = 'Enter your phone number...';
                phoneInput.required = true;
                phoneInput.style.cssText = 'width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;';
                form.appendChild(phoneInput);
            }

            // Button
            const btn = document.createElement('button');
            btn.type = 'submit';
            btn.textContent = newsletter.btn_text || 'Subscribe';
            btn.style.cssText = `
                background: #1a73e8; color: white; border: none;
                padding: 12px 24px; font-size: 16px; border-radius: 6px;
                cursor: pointer; width: 100%; font-weight: 600;
                margin-top: 10px;
            `;
            form.appendChild(btn);

            // Handle Submit
            form.onsubmit = (e) => {
                e.preventDefault();
                btn.textContent = 'Processing...';
                btn.disabled = true;

                const formData = new FormData(form);
                formData.append('w', WIDGET_ID);
                formData.append('nid', newsletter.id);

                fetch(`\${API_BASE}/submit-newsletter`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(res => {
                    if (res.success) {
                        if (newsletter.success_action === 'redirect' && newsletter.redirect_url) {
                            window.location.href = newsletter.redirect_url;
                        } else if (newsletter.success_action === 'close') {
                            const modal = document.getElementById(NEWSLETTER_CONTAINER_ID);
                            if (modal) {
                                modal.style.opacity = '0';
                                setTimeout(() => modal.remove(), 300);
                            }
                        } else {
                            // Show Message (replace form)
                            form.innerHTML = `<div style="padding: 20px; font-size: 18px; color: green;">\${newsletter.success_message || 'Thanks for subscribing!'}</div>`;
                        }
                    } else {
                        btn.textContent = 'Error. Try again.';
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    console.error(err);
                    btn.textContent = 'Error. Try again.';
                    btn.disabled = false;
                });
            };

            content.appendChild(form);
        });
    }

    // --- Social Widget Logic ---

    function checkSocials() {
        for (const social of socials) {
            if (shouldShowItem(social, 'social')) {
                // Check if session closed
                if (social.frequency === 'session' && sessionStorage.getItem(`trustabee_social_closed_\${social.id}`)) {
                    continue;
                }
                setupTrigger(social, showSocial);
                return;
            }
        }
    }

    function showSocial(social) {
        // We do NOT store "shown" timestamp in localStorage for frequency 'session'
        // because the requirement says "Close it for the session only".
        // This implies it shows on every page load unless closed in that session.
        // But if frequency is 'every_load', it shows every time.
        // Wait, standard triggers logic (shouldShowItem) handles frequency 'session' as "once every 30 mins".
        // The user requirement "close it for the session only" is a "Hide" logic, not a "Show" logic.
        // So we need to respect the explicit "close" action.

        if (sessionStorage.getItem(`trustabee_social_closed_\${social.id}`)) {
            return;
        }

        if (document.getElementById(SOCIAL_CONTAINER_ID)) return;

        trackSocialEvent(social.id, 'view');

        const container = document.createElement('div');
        container.id = SOCIAL_CONTAINER_ID;
        container.style.cssText = `
            position: fixed; bottom: 20px; right: 20px;
            width: 300px; background: white;
            border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            z-index: 10000; font-family: sans-serif;
            overflow: hidden; opacity: 0; transform: translateY(20px);
            transition: all 0.4s ease;
        `;

        // Header
        const header = document.createElement('div');
        header.style.cssText = `
            padding: 15px; text-align: center; border-bottom: 1px solid #f0f0f0;
            position: relative;
        `;

        // Close Button
        const closeBtn = document.createElement('div');
        closeBtn.innerHTML = '&times;';
        closeBtn.style.cssText = `
            position: absolute; top: 10px; right: 15px;
            font-size: 20px; cursor: pointer; color: #999;
            line-height: 1;
        `;
        closeBtn.onclick = () => {
            container.style.opacity = '0';
            container.style.transform = 'translateY(20px)';
            sessionStorage.setItem(`trustabee_social_closed_\${social.id}`, '1');
            setTimeout(() => container.remove(), 400);
        };
        header.appendChild(closeBtn);

        // Badge
        const badge = document.createElement('span');
        badge.textContent = social.title || 'Follow Us';
        badge.style.cssText = `
            background: #4ade80; color: #fff; padding: 4px 12px;
            border-radius: 20px; font-size: 12px; font-weight: bold;
            display: inline-block; margin-bottom: 8px;
        `;
        header.appendChild(badge);

        // Subtitle
        if (social.subtitle) {
            const sub = document.createElement('p');
            sub.textContent = social.subtitle;
            sub.style.cssText = `
                margin: 0; font-size: 13px; color: #666;
                line-height: 1.4; padding: 0 10px;
            `;
            header.appendChild(sub);
        }
        container.appendChild(header);

        // Links List
        const list = document.createElement('div');
        list.style.cssText = 'padding: 15px;';

        social.links.forEach(link => {
            const a = document.createElement('a');
            a.href = link.url;
            a.target = '_blank';
            a.style.cssText = `
                display: flex; align-items: center; text-decoration: none;
                padding: 10px; margin-bottom: 8px; border: 1px dashed #ddd;
                border-radius: 8px; color: #333; font-size: 14px;
                transition: background 0.2s;
            `;
            a.onmouseover = () => a.style.background = '#f9f9f9';
            a.onmouseout = () => a.style.background = 'transparent';
            a.onclick = () => {
                trackSocialEvent(social.id, 'click', link.id);
            };

            // Icon (Simple colored circles with first letter for now, or FontAwesome if available)
            // Since we don't have FA loaded necessarily, we use simple generic icons or specific colors.
            let iconColor = '#333';
            if (link.platform === 'facebook') iconColor = '#1877f2';
            if (link.platform === 'twitter') iconColor = '#1da1f2';
            if (link.platform === 'instagram') iconColor = '#c32aa3';
            if (link.platform === 'linkedin') iconColor = '#0a66c2';
            if (link.platform === 'youtube') iconColor = '#ff0000';
            if (link.platform === 'whatsapp') iconColor = '#25d366';
            if (link.platform === 'tiktok') iconColor = '#000000';
            if (link.platform === 'pinterest') iconColor = '#bd081c';
            if (link.platform === 'telegram') iconColor = '#0088cc';


            // We try to render an SVG if possible, otherwise a colored block
            const iconBox = document.createElement('span');
            iconBox.style.cssText = `
                width: 24px; height: 24px; background: \${iconColor};
                border-radius: 4px; margin-right: 10px; display: flex;
                align-items: center; justify-content: center; color: white;
                font-size: 14px; font-weight: bold;
            `;
            // Simple letter icon
            iconBox.textContent = link.platform.charAt(0).toUpperCase();

            a.appendChild(iconBox);

            const text = document.createElement('span');
            text.textContent = link.label_text || link.platform;
            text.style.fontWeight = '500';
            a.appendChild(text);

            list.appendChild(a);
        });
        container.appendChild(list);

        // Footer / Branding
        if (!social.remove_branding) {
            const footer = document.createElement('div');
            footer.style.cssText = `
                text-align: center; padding-bottom: 10px; font-size: 10px;
                color: #1a73e8; cursor: pointer;
            `;
            footer.textContent = 'Verified by Trustabee';
            footer.onclick = () => window.open('https://trustabee.io', '_blank');
            container.appendChild(footer);
        }

        document.body.appendChild(container);

        // Animate In
        requestAnimationFrame(() => {
            container.style.opacity = '1';
            container.style.transform = 'translateY(0)';
        });
    }

    // --- Generic Modal Builder ---

    function createModal(containerId, item, contentCallback) {
        const modal = document.createElement('div');
        modal.id = containerId;
        modal.style.cssText = `
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.6); z-index: 10000;
            display: flex; align-items: center; justify-content: center;
            font-family: sans-serif; opacity: 0; transition: opacity 0.3s;
        `;

        const content = document.createElement('div');
        content.style.cssText = `
            background: \${item.bg_color || '#fff'};
            color: \${item.text_color || '#333'};
            padding: 30px; border-radius: 12px;
            width: 90%; max-width: 450px;
            text-align: center; position: relative;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            transform: scale(0.9); transition: transform 0.3s;
        `;

        // Close Button
        const closeBtn = document.createElement('div');
        closeBtn.innerHTML = '&times;';
        closeBtn.style.cssText = `
            position: absolute; top: 10px; right: 15px;
            font-size: 24px; cursor: pointer; opacity: 0.6;
            z-index: 10;
        `;
        closeBtn.onclick = () => {
            modal.style.opacity = '0';
            setTimeout(() => modal.remove(), 300);
        };
        content.appendChild(closeBtn);

        // --- Image Layout Logic ---
        let contentContainer = content;
        let rightPane = null; // Used for split views

        if (item.image_url) {
            const imgUrl = API_BASE.replace('/api', '') + item.image_url;
            const style = item.image_style || 'top';

            if (style === 'background') {
                content.style.backgroundImage = `url('\${imgUrl}')`;
                content.style.backgroundSize = 'cover';
                content.style.backgroundPosition = 'center';
            } else if (style === 'top') {
                const img = document.createElement('img');
                img.src = imgUrl;
                img.style.cssText = 'width: 100%; height: 150px; object-fit: cover; border-radius: 8px 8px 0 0; margin-bottom: 20px; display: block; margin-left: -30px; margin-top: -30px; width: calc(100% + 60px);';
                content.appendChild(img);
            } else if (style === 'left' || style === 'right') {
                // Adjust Content Layout to Row
                content.style.display = 'flex';
                content.style.flexDirection = style === 'left' ? 'row' : 'row-reverse';
                content.style.maxWidth = '700px';
                content.style.padding = '0'; // Remove padding from main container
                content.style.overflow = 'hidden';

                const imgPane = document.createElement('div');
                imgPane.style.cssText = `
                    flex: 1; background-image: url('\${imgUrl}');
                    background-size: cover; background-position: center;
                    min-height: 300px;
                `;

                const textPane = document.createElement('div');
                textPane.style.cssText = 'flex: 1; padding: 30px; display: flex; flex-direction: column; justify-content: center; position: relative;';

                // We must append closeBtn to textPane to ensure it is visible on the white part
                // Or keep it absolute on 'content'. If 'content' has no padding/relative, absolute works.
                // But if image is on right (row-reverse), right: 15px puts X on image.
                // If image is on left, right: 15px puts X on text.
                // Let's move close button into text pane for better visibility/contrast.
                closeBtn.style.right = '15px';
                closeBtn.style.top = '10px';
                textPane.appendChild(closeBtn);

                content.appendChild(imgPane);
                content.appendChild(textPane);

                contentContainer = textPane; // All subsequent text/buttons go here
            }
        }

        // Title
        const title = document.createElement('h2');
        title.textContent = item.title;
        title.style.margin = '0 0 10px 0';
        contentContainer.appendChild(title);

        // Description / Message
        const descText = item.description || item.message;
        if (descText) {
            const desc = document.createElement('p');
            desc.textContent = descText;
            desc.style.cssText = 'margin: 0 0 20px 0; font-size: 16px; opacity: 0.9;';
            contentContainer.appendChild(desc);
        }

        // Callback for specific elements (coupon code or button)
        contentCallback(contentContainer);

        // Branding
        if (!item.remove_branding) {
             const branding = document.createElement('div');
             branding.textContent = 'Powered by Trustabee';
             branding.style.cssText = 'font-size: 10px; color: #999; margin-top: 15px; text-align: center; width: 100%;';
             contentContainer.appendChild(branding);
        }

        modal.appendChild(content);
        document.body.appendChild(modal);

        // Animate in
        requestAnimationFrame(() => {
            modal.style.opacity = '1';
            content.style.transform = 'scale(1)';
        });
    }

    // --- Widget Logic (Live Conversion) ---

    function createWidget() {
        if (document.getElementById(CONTAINER_ID)) return;

        widgetElement = document.createElement('div');
        widgetElement.id = CONTAINER_ID;
        widgetElement.className = 'sales-notification-widget hide';

        widgetElement.innerHTML = `
            <div class="map-placeholder"><img src="https://provely-public.s3.amazonaws.com/images/maps/default.jpg" alt="map" /></div>
            <div class="content">
                <p class="name"></p>
                <p class="action-text"></p>
                <div class="verification" style="display:none">
                    <span class="checkmark">&#x2713;</span>
                    <span class="verified-text">Verified by Trustabee</span>
                </div>
            </div>
        `;
        document.body.appendChild(widgetElement);
    }

    function updateWidgetContent() {
        const item = data[currentIndex];
        if (!item) return;

        const mapEl = widgetElement.querySelector('.map-placeholder');
        const nameEl = widgetElement.querySelector('.name');
        const actionEl = widgetElement.querySelector('.action-text');
        const verifyEl = widgetElement.querySelector('.verification');

        // Reset defaults
        verifyEl.style.display = 'none';
        mapEl.style.display = 'block';

        if (item.type === 'live_count') {
            mapEl.style.display = 'none'; // Hide map for simple count, or show eye icon
            nameEl.textContent = 'Live Visitors';
            actionEl.textContent = item.text;
        } else if (item.type === 'historical') {
            mapEl.style.display = 'none';
            nameEl.textContent = 'Popular';
            actionEl.textContent = item.text;
        } else {
            // Normal notification
            nameEl.textContent = item.name;
            actionEl.textContent = item.actionText;
            if (item.is_real) {
                verifyEl.style.display = 'flex';
            }
        }
    }

    function startCycle() {
        if (data.length === 0) return;

        updateWidgetContent();
        widgetElement.classList.remove('hide');

        setTimeout(() => {
            widgetElement.classList.add('hide');
            setTimeout(() => {
                currentIndex = (currentIndex + 1) % data.length;
                startCycle();
            }, HIDE_DURATION_MS);
        }, DISPLAY_DURATION_MS);
    }

    function injectStyles(config) {
        let bottom = '20px';
        let left = '20px';
        let right = 'auto';
        let bgColor = 'white';
        let textColor = '#333';

        if (config) {
            if (config.position === 'bottom-right') {
                left = 'auto';
                right = '20px';
            }
            if (config.bg_color) bgColor = config.bg_color;
            if (config.text_color) textColor = config.text_color;
        }

        const style = document.createElement('style');
        style.innerHTML = `
            .sales-notification-widget {
                position: fixed;
                bottom: \${bottom};
                left: \${left};
                right: \${right};
                z-index: 9999;
                background: \${bgColor};
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                padding: 10px;
                display: flex;
                align-items: center;
                width: 300px;
                opacity: 1;
                transform: translateX(0);
                transition: opacity 0.5s, transform 0.5s;
                font-family: sans-serif;
                color: \${textColor};
            }
            .sales-notification-widget.hide {
                opacity: 0;
                transform: translateX(\${left === 'auto' ? '150%' : '-150%'});
            }
            .sales-notification-widget .map-placeholder { width: 50px; height: 50px; background: #eee; border-radius: 4px; margin-right: 10px; flex-shrink: 0; overflow: hidden; }
            .sales-notification-widget .map-placeholder img { width: 100%; height: 100%; object-fit: cover; }
            .sales-notification-widget .content { display: flex; flex-direction: column; justify-content: center; flex-grow: 1; line-height: 1.2; }
            .sales-notification-widget .name { font-weight: 700; color: inherit; font-size: 14px; margin: 0; }
            .sales-notification-widget .action-text { margin: 2px 0 5px 0; color: inherit; opacity: 0.8; font-size: 13px; }
            .sales-notification-widget .verification { display: flex; align-items: center; font-size: 11px; color: #1a73e8; font-weight: 500; }
            .sales-notification-widget .checkmark { font-weight: bold; margin-right: 4px; }
        `;
        document.head.appendChild(style);
    }

    function initWidget(config) {
        injectStyles(config);
        createWidget();
        startCycle();
    }

    function enableMagicalDetection() {
        document.addEventListener('submit', function(e) {
            const form = e.target;
            const formData = new FormData(form);
            trackConversion(formData);
        });
    }

    setInterval(sendHeartbeat, 30000);
    sendHeartbeat();
    fetchData();

})();
JS;
    }

    public function getData() {
        $this->addCors();
        header('Content-Type: application/json');

        $widget_id = $_GET['w'] ?? 0;
        $pdo = Database::getInstance();

        $stmt = $pdo->prepare("SELECT * FROM widgets WHERE id = ?");
        $stmt->execute([$widget_id]);
        $widget = $stmt->fetch();

        if (!$widget) {
            echo json_encode(['error' => 'Widget not found']);
            return;
        }

        // Lazy Logging: Check if we need to take a snapshot
        $this->logTrafficSnapshot($widget_id, $pdo);

        // Fetch active coupons
        $stmt = $pdo->prepare("SELECT * FROM coupons WHERE widget_id = ? AND active = 1");
        $stmt->execute([$widget_id]);
        $coupons = $stmt->fetchAll();

        // Fetch active announcements
        $stmt = $pdo->prepare("SELECT * FROM announcements WHERE widget_id = ? AND active = 1");
        $stmt->execute([$widget_id]);
        $announcements = $stmt->fetchAll();

        // Fetch active videos
        $stmt = $pdo->prepare("SELECT * FROM videos WHERE widget_id = ? AND active = 1");
        $stmt->execute([$widget_id]);
        $videos = $stmt->fetchAll();

        // Fetch active newsletters
        $stmt = $pdo->prepare("SELECT * FROM newsletters WHERE widget_id = ? AND active = 1");
        $stmt->execute([$widget_id]);
        $newsletters = $stmt->fetchAll();

        // Fetch active social widgets
        $stmt = $pdo->prepare("SELECT * FROM socials WHERE widget_id = ? AND active = 1");
        $stmt->execute([$widget_id]);
        $socials = $stmt->fetchAll();

        // Attach links to socials
        foreach ($socials as &$social) {
            $stmt = $pdo->prepare("SELECT * FROM social_links WHERE social_id = ? AND is_active = 1 ORDER BY sort_order ASC, id ASC LIMIT 5");
            $stmt->execute([$social['id']]);
            $social['links'] = $stmt->fetchAll();
        }

        // 1. Live Visitors (Active in last 30 minutes, distinct)
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT visitor_id) as count FROM live_visitors WHERE widget_id = ? AND last_seen > (NOW() - INTERVAL 30 MINUTE)");
        $stmt->execute([$widget_id]);
        $live_count = $stmt->fetch()['count'];

        // 2. Historical (Last 7 days events)
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM events WHERE widget_id = ? AND created_at > (NOW() - INTERVAL 7 DAY)");
        $stmt->execute([$widget_id]);
        $historical_count = $stmt->fetch()['count'];

        $notifications = [];

        // Check if Live Conversion is enabled
        $live_conversion_enabled = (bool)($widget['live_conversion_enabled'] ?? false);
        $use_real = (bool)($widget['use_real_conversion'] ?? true);
        $use_simulated = (bool)($widget['use_simulated_conversion'] ?? true);

        if ($live_conversion_enabled) {
            // 3. Simulated Data
            if ($use_simulated) {
                $stmt = $pdo->prepare("SELECT name, action_text as actionText, location, image_url FROM notifications WHERE widget_id = ? AND active = 1");
                $stmt->execute([$widget_id]);
                $simulated = $stmt->fetchAll();
                foreach ($simulated as &$s) { $s['is_real'] = false; }
                $notifications = array_merge($notifications, $simulated);
            }

            // 4. Real Data
            if ($use_real) {
                $stmt = $pdo->prepare("SELECT * FROM events WHERE widget_id = ? AND type='form_submit' ORDER BY created_at DESC LIMIT 5");
                $stmt->execute([$widget_id]);
                $real_events = $stmt->fetchAll();

                $real = [];
                foreach ($real_events as $ev) {
                    $real[] = [
                        'name' => 'A visitor',
                        'actionText' => 'Just signed up',
                        'is_real' => true
                    ];
                }
                $notifications = array_merge($notifications, $real);
            }
        }

        $live_config = json_decode($widget['live_visitor_config'] ?? '{}', true);

        echo json_encode([
            'config' => [
                'magical_detection' => (bool)$widget['magical_detection'],
                'live_visitor_enabled' => (bool)($widget['live_visitor_enabled'] ?? false),
                'live_visitor_config' => $live_config
            ],
            'coupons' => $coupons,
            'announcements' => $announcements,
            'videos' => $videos,
            'newsletters' => $newsletters,
            'socials' => $socials,
            'live_count' => $live_count,
            'historical_count' => $historical_count,
            'notifications' => $notifications
        ]);
    }

    private function logTrafficSnapshot($widget_id, $pdo) {
        // Check for any snapshot in the last 5 minutes (to avoid race conditions/duplicates)
        $stmt = $pdo->prepare("SELECT id FROM traffic_snapshots WHERE widget_id = ? AND created_at > (NOW() - INTERVAL 5 MINUTE) LIMIT 1");
        $stmt->execute([$widget_id]);
        $recent_exists = $stmt->fetchColumn();

        if (!$recent_exists) {
            // Take snapshot
            // Count distinct visitors active in last 30 mins
            $stmt = $pdo->prepare("SELECT COUNT(DISTINCT visitor_id) FROM live_visitors WHERE widget_id = ? AND last_seen > (NOW() - INTERVAL 30 MINUTE)");
            $stmt->execute([$widget_id]);
            $count = $stmt->fetchColumn();

            $stmt = $pdo->prepare("INSERT INTO traffic_snapshots (widget_id, visitor_count, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$widget_id, $count]);
        }
    }

    public function heartbeat() {
        $this->addCors();
        $widget_id = $_POST['w'] ?? 0;
        $visitor_id = $_POST['vid'] ?? 'unknown';
        $url = $_POST['url'] ?? '';

        if (!$widget_id) return;

        $pdo = Database::getInstance();

        $stmt = $pdo->prepare("SELECT id FROM live_visitors WHERE widget_id = ? AND visitor_id = ?");
        $stmt->execute([$widget_id, $visitor_id]);
        $exists = $stmt->fetch();

        if ($exists) {
            $stmt = $pdo->prepare("UPDATE live_visitors SET last_seen = NOW(), url = ? WHERE id = ?");
            $stmt->execute([$url, $exists['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO live_visitors (widget_id, visitor_id, url, last_seen) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$widget_id, $visitor_id, $url]);
        }
    }

    public function trackEvent() {
        $this->addCors();
        $widget_id = $_POST['w'] ?? 0;
        $type = $_POST['type'] ?? 'unknown';
        $payload = $_POST['payload'] ?? '';
        $visitor_id = $_POST['vid'] ?? null;
        $page = $_POST['page'] ?? '';

        if (!$widget_id) return;

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("INSERT INTO events (widget_id, type, payload, visitor_id, page_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$widget_id, $type, $payload, $visitor_id, $page]);
    }

    public function trackCoupon() {
        $this->addCors();
        $widget_id = $_POST['w'] ?? 0;
        $coupon_id = $_POST['cid'] ?? 0;
        $type = $_POST['type'] ?? 'unknown';

        if (!$widget_id || !$coupon_id) return;

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("INSERT INTO coupon_analytics (coupon_id, event_type, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$coupon_id, $type]);
    }

    public function trackAnnouncement() {
        $this->addCors();
        $widget_id = $_POST['w'] ?? 0;
        $announcement_id = $_POST['aid'] ?? 0;
        $type = $_POST['type'] ?? 'unknown';

        if (!$widget_id || !$announcement_id) return;

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("INSERT INTO announcement_analytics (announcement_id, event_type, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$announcement_id, $type]);
    }

    public function trackVideo() {
        $this->addCors();
        $widget_id = $_POST['w'] ?? 0;
        $video_id = $_POST['vid'] ?? 0;
        $type = $_POST['type'] ?? 'unknown';

        if (!$widget_id || !$video_id) return;

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("INSERT INTO video_analytics (video_id, event_type, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$video_id, $type]);
    }

    public function trackNewsletter() {
        $this->addCors();
        $widget_id = $_POST['w'] ?? 0;
        $newsletter_id = $_POST['nid'] ?? 0;
        $type = $_POST['type'] ?? 'unknown';

        if (!$widget_id || !$newsletter_id) return;

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("INSERT INTO newsletter_analytics (newsletter_id, event_type, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$newsletter_id, $type]);
    }

    public function trackSocial() {
        $this->addCors();
        $widget_id = $_POST['w'] ?? 0;
        $social_id = $_POST['sid'] ?? 0;
        $type = $_POST['type'] ?? 'unknown';
        $link_id = $_POST['lid'] ?? null;

        if (!$widget_id || !$social_id) return;

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("INSERT INTO social_analytics (social_id, link_id, event_type, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$social_id, $link_id, $type]);
    }

    public function submitNewsletter() {
        $this->addCors();
        $widget_id = $_POST['w'] ?? 0;
        $newsletter_id = $_POST['nid'] ?? 0;

        $email = $_POST['email'] ?? '';
        $name = $_POST['name'] ?? null;
        $phone = $_POST['phone'] ?? null;

        if (!$widget_id || !$newsletter_id || !$email) {
             echo json_encode(['error' => 'Missing required fields']);
             return;
        }

        $pdo = Database::getInstance();

        // Save Lead
        $stmt = $pdo->prepare("INSERT INTO newsletter_leads (newsletter_id, widget_id, email, name, phone, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$newsletter_id, $widget_id, $email, $name, $phone]);

        // Track 'submit' event
        $stmt = $pdo->prepare("INSERT INTO newsletter_analytics (newsletter_id, event_type, created_at) VALUES (?, 'submit', NOW())");
        $stmt->execute([$newsletter_id]);

        // Webhook
        $stmt = $pdo->prepare("SELECT webhook_url FROM newsletters WHERE id = ?");
        $stmt->execute([$newsletter_id]);
        $webhook_url = $stmt->fetchColumn();

        if ($webhook_url) {
             $data = [
                 'widget_id' => $widget_id,
                 'newsletter_id' => $newsletter_id,
                 'email' => $email,
                 'name' => $name,
                 'phone' => $phone,
                 'created_at' => date('Y-m-d H:i:s')
             ];

             // Fire and forget (or with short timeout)
             $ch = curl_init($webhook_url);
             curl_setopt($ch, CURLOPT_POST, 1);
             curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
             curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
             curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 seconds timeout
             curl_exec($ch);
             curl_close($ch);
        }

        echo json_encode(['success' => true]);
    }

    private function addCors() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit(0);
        }
    }
}
