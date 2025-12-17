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
    const REVIEW_CONTAINER_ID = 'trustabee-review-modal';

    // Social Media Icons (SVG Paths) - 24x24 ViewBox preferred
    const SOCIAL_ICONS = {
        'facebook': 'M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.085 1.848-5.978 5.858-5.978.401 0 .955.042 1.468.103a8.68 8.68 0 0 1 1.141.195v3.325a8.623 8.623 0 0 0-.653-.036 26.805 26.805 0 0 0-.733-.009c-.707 0-1.259.096-1.675.309a1.686 1.686 0 0 0-.679.622c-.258.42-.374.995-.374 1.752v1.297h3.919l-.386 2.103-.287 1.564h-3.246v8.245C19.396 23.238 24 18.179 24 12.044c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.628 3.874 10.35 9.101 11.647Z',
        'twitter': 'M14.234 10.162 22.977 0h-2.072l-7.591 8.824L7.251 0H.258l9.168 13.343L.258 24H2.33l8.016-9.318L16.749 24h6.993zm-2.837 3.299-.929-1.329L3.076 1.56h3.182l5.965 8.532.929 1.329 7.754 11.09h-3.182z',
        'instagram': 'M7.0301.084c-1.2768.0602-2.1487.264-2.911.5634-.7888.3075-1.4575.72-2.1228 1.3877-.6652.6677-1.075 1.3368-1.3802 2.127-.2954.7638-.4956 1.6365-.552 2.914-.0564 1.2775-.0689 1.6882-.0626 4.947.0062 3.2586.0206 3.6671.0825 4.9473.061 1.2765.264 2.1482.5635 2.9107.308.7889.72 1.4573 1.388 2.1228.6679.6655 1.3365 1.0743 2.1285 1.38.7632.295 1.6361.4961 2.9134.552 1.2773.056 1.6884.069 4.9462.0627 3.2578-.0062 3.668-.0207 4.9478-.0814 1.28-.0607 2.147-.2652 2.9098-.5633.7889-.3086 1.4578-.72 2.1228-1.3881.665-.6682 1.0745-1.3378 1.3795-2.1284.2957-.7632.4966-1.636.552-2.9124.056-1.2809.0692-1.6898.063-4.948-.0063-3.2583-.021-3.6668-.0817-4.9465-.0607-1.2797-.264-2.1487-.5633-2.9117-.3084-.7889-.72-1.4568-1.3876-2.1228C21.2982 1.33 20.628.9208 19.8378.6165 19.074.321 18.2017.1197 16.9244.0645 15.6471.0093 15.236-.005 11.977.0014 8.718.0076 8.31.0215 7.0301.0839m.1402 21.6932c-1.17-.0509-1.8053-.2453-2.2287-.408-.5606-.216-.96-.4771-1.3819-.895-.422-.4178-.6811-.8186-.9-1.378-.1644-.4234-.3624-1.058-.4171-2.228-.0595-1.2645-.072-1.6442-.079-4.848-.007-3.2037.0053-3.583.0607-4.848.05-1.169.2456-1.805.408-2.2282.216-.5613.4762-.96.895-1.3816.4188-.4217.8184-.6814 1.3783-.9003.423-.1651 1.0575-.3614 2.227-.4171 1.2655-.06 1.6447-.072 4.848-.079 3.2033-.007 3.5835.005 4.8495.0608 1.169.0508 1.8053.2445 2.228.408.5608.216.96.4754 1.3816.895.4217.4194.6816.8176.9005 1.3787.1653.4217.3617 1.056.4169 2.2263.0602 1.2655.0739 1.645.0796 4.848.0058 3.203-.0055 3.5834-.061 4.848-.051 1.17-.245 1.8055-.408 2.2294-.216.5604-.4763.96-.8954 1.3814-.419.4215-.8181.6811-1.3783.9-.4224.1649-1.0577.3617-2.2262.4174-1.2656.0595-1.6448.072-4.8493.079-3.2045.007-3.5825-.006-4.848-.0608M16.953 5.5864A1.44 1.44 0 1 0 18.39 4.144a1.44 1.44 0 0 0-1.437 1.4424M5.8385 12.012c.0067 3.4032 2.7706 6.1557 6.173 6.1493 3.4026-.0065 6.157-2.7701 6.1506-6.1733-.0065-3.4032-2.771-6.1565-6.174-6.1498-3.403.0067-6.156 2.771-6.1496 6.1738M8 12.0077a4 4 0 1 1 4.008 3.9921A3.9996 3.9996 0 0 1 8 12.0077',
        'linkedin': 'M21,21H17V14.25C17,13.19 15.81,12.31 14.75,12.31C13.69,12.31 13,13.19 13,14.25V21H9V9H13V11C13.66,9.93 15.36,9.24 16.5,9.24C19,9.24 21,11.28 21,13.75V21M7,21H3V9H7V21M5,3A2,2 0 0,1 7,5A2,2 0 0,1 5,7A2,2 0 0,1 3,5A2,2 0 0,1 5,3Z',
        'youtube': 'M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z',
        'whatsapp': 'M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z',
        'tiktok': 'M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z',
        'pinterest': 'M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z',
        'telegram': 'M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z',
        'discord': 'M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9555 2.4189-2.1569 2.4189zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.4189-2.1568 2.4189Z',
        'reddit': 'M12 0C5.373 0 0 5.373 0 12c0 3.314 1.343 6.314 3.515 8.485l-2.286 2.286C.775 23.225 1.097 24 1.738 24H12c6.627 0 12-5.373 12-12S18.627 0 12 0Zm4.388 3.199c1.104 0 1.999.895 1.999 1.999 0 1.105-.895 2-1.999 2-.946 0-1.739-.657-1.947-1.539v.002c-1.147.162-2.032 1.15-2.032 2.341v.007c1.776.067 3.4.567 4.686 1.363.473-.363 1.064-.58 1.707-.58 1.547 0 2.802 1.254 2.802 2.802 0 1.117-.655 2.081-1.601 2.531-.088 3.256-3.637 5.876-7.997 5.876-4.361 0-7.905-2.617-7.998-5.87-.954-.447-1.614-1.415-1.614-2.538 0-1.548 1.255-2.802 2.803-2.802.645 0 1.239.218 1.712.585 1.275-.79 2.881-1.291 4.64-1.365v-.01c0-1.663 1.263-3.034 2.88-3.207.188-.911.993-1.595 1.959-1.595Zm-8.085 8.376c-.784 0-1.459.78-1.506 1.797-.047 1.016.64 1.429 1.426 1.429.786 0 1.371-.369 1.418-1.385.047-1.017-.553-1.841-1.338-1.841Zm7.406 0c-.786 0-1.385.824-1.338 1.841.047 1.017.634 1.385 1.418 1.385.785 0 1.473-.413 1.426-1.429-.046-1.017-.721-1.797-1.506-1.797Zm-3.703 4.013c-.974 0-1.907.048-2.77.135-.147.015-.241.168-.183.305.483 1.154 1.622 1.964 2.953 1.964 1.33 0 2.47-.81 2.953-1.964.057-.137-.037-.29-.184-.305-.863-.087-1.795-.135-2.769-.135Z',
        'snapchat': 'M12.206.793c.99 0 4.347.276 5.93 3.821.529 1.193.403 3.219.299 4.847l-.003.06c-.012.18-.022.345-.03.51.075.045.203.09.401.09.3-.016.659-.12 1.033-.301.165-.088.344-.104.464-.104.182 0 .359.029.509.09.45.149.734.479.734.838.015.449-.39.839-1.213 1.168-.089.029-.209.075-.344.119-.45.135-1.139.36-1.333.81-.09.224-.061.524.12.868l.015.015c.06.136 1.526 3.475 4.791 4.014.255.044.435.27.42.509 0 .075-.015.149-.045.225-.24.569-1.273.988-3.146 1.271-.059.091-.12.375-.164.57-.029.179-.074.36-.134.553-.076.271-.27.405-.555.405h-.03c-.135 0-.313-.031-.538-.074-.36-.075-.765-.135-1.273-.135-.3 0-.599.015-.913.074-.6.104-1.123.464-1.723.884-.853.599-1.826 1.288-3.294 1.288-.06 0-.119-.015-.18-.015h-.149c-1.468 0-2.427-.675-3.279-1.288-.599-.42-1.107-.779-1.707-.884-.314-.045-.629-.074-.928-.074-.54 0-.958.089-1.272.149-.211.043-.391.074-.54.074-.374 0-.523-.224-.583-.42-.061-.192-.09-.389-.135-.567-.046-.181-.105-.494-.166-.57-1.918-.222-2.95-.642-3.189-1.226-.031-.063-.052-.15-.055-.225-.015-.243.165-.465.42-.509 3.264-.54 4.73-3.879 4.791-4.02l.016-.029c.18-.345.224-.645.119-.869-.195-.434-.884-.658-1.332-.809-.121-.029-.24-.074-.346-.119-1.107-.435-1.257-.93-1.197-1.273.09-.479.674-.793 1.168-.793.146 0 .27.029.383.074.42.194.789.3 1.104.3.234 0 .384-.06.465-.105l-.046-.569c-.098-1.626-.225-3.651.307-4.837C7.392 1.077 10.739.807 11.727.807l.419-.015h.06z',
        'spotify': 'M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z'
    };

    let data = [];
    let coupons = [];
    let announcements = [];
    let videos = [];
    let newsletters = [];
    let socials = [];
    let reviewConfig = null;
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

            // Store Review Config (Popup)
            if (json.review_config && json.review_config.active == 1) {
                reviewConfig = json.review_config;
                checkReviews();
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

    function trackReviewEvent(reviewId, eventType) {
        const payload = new URLSearchParams();
        payload.append('w', WIDGET_ID);
        payload.append('rid', reviewId);
        payload.append('type', eventType);

        if (navigator.sendBeacon) {
            navigator.sendBeacon(`\${API_BASE}/track-review`, payload);
        } else {
            fetch(`\${API_BASE}/track-review`, { method: 'POST', body: payload });
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

    // --- Collision Detection ---
    function isAnyModalOpen() {
        const ids = [
            COUPON_CONTAINER_ID,
            ANNOUNCEMENT_CONTAINER_ID,
            VIDEO_CONTAINER_ID,
            NEWSLETTER_CONTAINER_ID,
            REVIEW_CONTAINER_ID
        ];
        for (const id of ids) {
            if (document.getElementById(id)) return true;
        }
        return false;
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

        if (isAnyModalOpen()) {
            setTimeout(() => showCoupon(coupon), 1000);
            return;
        }

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

        if (isAnyModalOpen()) {
            setTimeout(() => showAnnouncement(announcement), 1000);
            return;
        }

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

        if (isAnyModalOpen()) {
            setTimeout(() => showVideo(video), 1000);
            return;
        }

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

        if (isAnyModalOpen()) {
            setTimeout(() => showNewsletter(newsletter), 1000);
            return;
        }

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

    // --- Review Popup Logic ---

    function checkReviews() {
        if (!reviewConfig) return;

        // Adapt properties for generic handler
        // Ensure numeric types for delay and verify structure
        const item = {
            ...reviewConfig,
            trigger_delay: parseInt(reviewConfig.trigger_delay || 0),
            trigger_type: reviewConfig.trigger_type || 'delay',
            frequency: reviewConfig.frequency || 'every_load',
            match_url: reviewConfig.match_url || ''
        };

        if (shouldShowItem(item, 'review_popup')) {
             setupTrigger(item, showReviewPopup);
        }
    }

    function showReviewPopup(config) {
        const storageKey = `trustabee_review_popup_shown_\${config.id}`;
        localStorage.setItem(storageKey, new Date().getTime());

        if (document.getElementById(REVIEW_CONTAINER_ID)) return;

        if (isAnyModalOpen()) {
            setTimeout(() => showReviewPopup(config), 1000);
            return;
        }

        trackReviewEvent(config.id, 'view_popup');

        // Manual Modal Build (since it has custom logic)
        const modal = document.createElement('div');
        modal.id = REVIEW_CONTAINER_ID;
        modal.style.cssText = `
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.6); z-index: 10000;
            display: flex; align-items: center; justify-content: center;
            font-family: sans-serif; opacity: 0; transition: opacity 0.3s;
        `;

        const content = document.createElement('div');
        content.style.cssText = `
            background: #fff; color: #333;
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

        // Title
        const title = document.createElement('h2');
        title.textContent = config.popup_title || 'Rate your experience';
        title.style.margin = '0 0 10px 0';
        content.appendChild(title);

        // Desc
        if (config.popup_description) {
            const desc = document.createElement('p');
            desc.textContent = config.popup_description;
            desc.style.cssText = 'margin: 0 0 20px 0; font-size: 16px; opacity: 0.8;';
            content.appendChild(desc);
        }

        // Stars Container
        const stars = document.createElement('div');
        stars.style.fontSize = '32px';
        stars.style.color = '#ccc';
        stars.style.cursor = 'pointer';
        stars.style.marginBottom = '20px';

        for (let i = 1; i <= 5; i++) {
            const star = document.createElement('span');
            star.innerHTML = '&#9733;'; // Star char
            star.dataset.value = i;
            star.style.margin = '0 5px';
            star.onmouseover = () => highlightStars(i);
            star.onmouseout = () => highlightStars(0);
            star.onclick = () => handleRating(i, config, content, modal);
            stars.appendChild(star);
        }

        function highlightStars(val) {
            Array.from(stars.children).forEach(s => {
                 s.style.color = (parseInt(s.dataset.value) <= val) ? '#fbbf24' : '#ccc';
            });
        }

        content.appendChild(stars);

        // Branding
        if (!config.remove_branding) {
             const branding = document.createElement('div');
             branding.textContent = 'Powered by Trustabee';
             branding.style.cssText = 'font-size: 10px; color: #999; margin-top: 15px;';
             content.appendChild(branding);
        }

        modal.appendChild(content);
        document.body.appendChild(modal);

        requestAnimationFrame(() => {
            modal.style.opacity = '1';
            content.style.transform = 'scale(1)';
        });
    }

    function showReviewPlatformChoice(googleUrl, fbUrl, config, container, modal) {
        container.innerHTML = '';

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
        container.appendChild(closeBtn);

        const title = document.createElement('h3');
        title.textContent = 'Thank you! Where would you like to leave a review?';
        title.style.marginBottom = '20px';
        container.appendChild(title);

        const btnContainer = document.createElement('div');
        btnContainer.style.display = 'flex';
        btnContainer.style.flexDirection = 'column';
        btnContainer.style.gap = '10px';
        btnContainer.style.padding = '0 20px';

        // Google Button
        const gBtn = document.createElement('button');
        gBtn.textContent = 'Review on Google';
        gBtn.style.cssText = `
            background: #DB4437; color: white; border: none;
            padding: 12px; font-size: 16px; border-radius: 6px;
            cursor: pointer; font-weight: 600; width: 100%;
        `;
        gBtn.onclick = () => {
             window.open(googleUrl, '_blank');
             triggerPostAction();
        };
        btnContainer.appendChild(gBtn);

        // Facebook Button
        const fbBtn = document.createElement('button');
        fbBtn.textContent = 'Review on Facebook';
        fbBtn.style.cssText = `
            background: #1877F2; color: white; border: none;
            padding: 12px; font-size: 16px; border-radius: 6px;
            cursor: pointer; font-weight: 600; width: 100%;
        `;
        fbBtn.onclick = () => {
             window.open(fbUrl, '_blank');
             triggerPostAction();
        };
        btnContainer.appendChild(fbBtn);

        container.appendChild(btnContainer);

        function triggerPostAction() {
             container.innerHTML = '<div style="padding:40px;">Processing...</div>';
             setTimeout(() => {
                 handlePostAction(config, 'high', container, modal);
             }, 2000);
        }
    }

    function handleRating(rating, config, container, modal) {
        trackReviewEvent(config.id, 'click_star_' + rating);

        if (rating >= 4) {
             // HIGH RATING Logic
             const google = config.google_review_link;
             const facebook = config.facebook_review_link;

             if (google && google.trim() !== '' && facebook && facebook.trim() !== '') {
                 showReviewPlatformChoice(google, facebook, config, container, modal);
             } else {
                 let url = google || facebook;
                 if (url) {
                     window.open(url, '_blank');
                 }

                 // Wait 3 seconds then show Post Action
                 container.innerHTML = '<div style="padding:40px;">Processing...</div>';
                 setTimeout(() => {
                     handlePostAction(config, 'high', container, modal);
                 }, 3000);
             }
        } else {
            // LOW RATING Logic (Form)
            showFeedbackForm(config, container, modal, rating);
        }
    }

    function showFeedbackForm(config, container, modal, rating) {
        container.innerHTML = '';

        const title = document.createElement('h3');
        title.textContent = 'How can we improve?';
        container.appendChild(title);

        const form = document.createElement('form');
        form.style.textAlign = 'left';

        const nameIn = document.createElement('input');
        nameIn.placeholder = 'Name';
        nameIn.name = 'name';
        nameIn.style.cssText = 'width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box;';
        form.appendChild(nameIn);

        const emailIn = document.createElement('input');
        emailIn.placeholder = 'Email';
        emailIn.name = 'email';
        emailIn.type = 'email';
        emailIn.style.cssText = 'width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box;';
        form.appendChild(emailIn);

        const fbIn = document.createElement('textarea');
        fbIn.placeholder = 'Your feedback...';
        fbIn.name = 'feedback';
        fbIn.rows = 3;
        fbIn.style.cssText = 'width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box;';
        form.appendChild(fbIn);

        const btn = document.createElement('button');
        btn.textContent = 'Submit Feedback';
        btn.style.cssText = 'width: 100%; padding: 10px; background: #1a73e8; color: white; border: none; border-radius: 4px; cursor: pointer;';
        form.appendChild(btn);

        form.onsubmit = (e) => {
            e.preventDefault();
            const fd = new FormData(form);
            fd.append('w', WIDGET_ID);
            fd.append('rid', config.id);
            fd.append('rating', rating);

            fetch(`\${API_BASE}/submit-review`, {method:'POST', body:fd})
            .then(r=>r.json())
            .then(res => {
                 handlePostAction(config, 'low', container, modal);
            });
        };

        container.appendChild(form);

        // Add close button back
        const closeBtn = document.createElement('div');
        closeBtn.innerHTML = '&times;';
        closeBtn.style.cssText = `
            position: absolute; top: 10px; right: 15px;
            font-size: 24px; cursor: pointer; opacity: 0.6;
        `;
        closeBtn.onclick = () => { modal.remove(); };
        container.appendChild(closeBtn);
    }

    function handlePostAction(config, type, container, modal) {
        const action = config[type + '_star_action'];
        const message = config[type + '_star_message'];
        const redirect = config[type + '_star_redirect_url'];
        const couponId = config[type + '_star_coupon_id'];

        container.innerHTML = '';

        if (action === 'close') {
            modal.remove();
        } else if (action === 'redirect' && redirect) {
            window.location.href = redirect;
        } else if (action === 'coupon' && couponId) {
            // Find coupon data
            const coupon = coupons.find(c => c.id == couponId);
            if (coupon) {
                // Show Coupon Modal logic (reuse showCoupon?)
                // Since showCoupon creates a new modal, let's close this one and call showCoupon
                modal.remove();
                showCoupon(coupon);
            } else {
                container.innerHTML = '<p>Coupon not found.</p>';
                setTimeout(() => modal.remove(), 2000);
            }
        } else {
            // Thank You Message
            container.innerHTML = `<div style="padding:40px; color: green; font-size: 18px;">\${message || 'Thank you!'}</div>`;
            setTimeout(() => modal.remove(), 3000);
        }
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

        // Position Logic
        let positionStyle = 'bottom: 20px; right: 20px;';
        if (social.position === 'bottom-left') {
            positionStyle = 'bottom: 20px; left: 20px;';
        }

        container.style.cssText = `
            position: fixed; \${positionStyle}
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

            // Icon (SVG Icons or Fallback)
            let iconColor = '#333';
            if (link.platform === 'facebook') iconColor = '#1877f2';
            if (link.platform === 'twitter') iconColor = '#000000'; // X is black
            if (link.platform === 'instagram') iconColor = '#c32aa3';
            if (link.platform === 'linkedin') iconColor = '#0a66c2';
            if (link.platform === 'youtube') iconColor = '#ff0000';
            if (link.platform === 'whatsapp') iconColor = '#25d366';
            if (link.platform === 'tiktok') iconColor = '#000000';
            if (link.platform === 'pinterest') iconColor = '#bd081c';
            if (link.platform === 'telegram') iconColor = '#0088cc';
            if (link.platform === 'discord') iconColor = '#5865F2';
            if (link.platform === 'reddit') iconColor = '#FF4500';
            if (link.platform === 'snapchat') iconColor = '#FFFC00';
            if (link.platform === 'spotify') iconColor = '#1DB954';

            const iconBox = document.createElement('span');
            iconBox.style.cssText = `
                width: 24px; height: 24px; background: \${iconColor};
                border-radius: 4px; margin-right: 10px; display: flex;
                align-items: center; justify-content: center; color: white;
                font-size: 14px; font-weight: bold; overflow: hidden;
            `;

            if (SOCIAL_ICONS[link.platform]) {
                // Render SVG
                const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                svg.setAttribute('viewBox', '0 0 24 24');
                svg.style.width = '16px';
                svg.style.height = '16px';
                svg.style.fill = 'white';

                // SnapChat logo is usually black on yellow
                if (link.platform === 'snapchat') {
                    svg.style.fill = 'black';
                }

                const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                path.setAttribute('d', SOCIAL_ICONS[link.platform]);
                svg.appendChild(path);
                iconBox.appendChild(svg);
            } else {
                // Fallback: First letter
                iconBox.textContent = link.platform.charAt(0).toUpperCase();
            }

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
            mapEl.style.display = 'none';
            nameEl.textContent = 'Live Visitors';
            actionEl.textContent = item.text;
        } else if (item.type === 'historical') {
            mapEl.style.display = 'none';
            nameEl.textContent = 'Popular';
            actionEl.textContent = item.text;
        } else if (item.type === 'review') {
            // Review Item in Toaster
            mapEl.style.display = 'block';

            // Image
            if (item.image_url) {
                mapEl.innerHTML = `<img src="\${item.image_url}" alt="user" />`;
            } else {
                // Fallback: First letter
                const letter = item.name.charAt(0).toUpperCase();
                mapEl.innerHTML = `<div style="width:100%;height:100%;background:#1a73e8;color:white;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:24px;">\${letter}</div>`;
            }

            // Stars
            let stars = '';
            for(let i=0; i<item.rating; i++) stars += '&#9733;';

            nameEl.innerHTML = `\${item.name} <span style="color:#fbbf24">\${stars}</span>`;

            // Text + Link
            let text = item.review_text.substring(0, 50);
            if(item.review_text.length > 50) text += '...';

            let sourceHtml = '';
            if (item.source !== 'custom' && item.source_link) {
                 const sName = item.source.charAt(0).toUpperCase() + item.source.slice(1);
                 sourceHtml = ` <a href="\${item.source_link}" target="_blank" style="color:#1a73e8;text-decoration:none;font-size:11px;">View on \${sName}</a>`;
            }

            actionEl.innerHTML = `"\${text}"\${sourceHtml}`;

        } else {
            // Normal notification
            // Reset map
            mapEl.innerHTML = `<img src="https://provely-public.s3.amazonaws.com/images/maps/default.jpg" alt="map" />`;

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

        // Fetch active review configuration
        $stmt = $pdo->prepare("SELECT * FROM reviews WHERE widget_id = ? AND active = 1");
        $stmt->execute([$widget_id]);
        $reviews_config = $stmt->fetch(); // Only one config per widget

        $review_items = [];
        if ($reviews_config) {
             // Fetch manual reviews for toaster if enabled
             if ($reviews_config['show_reviews_widget']) {
                 $stmt = $pdo->prepare("SELECT * FROM review_items WHERE review_id = ? AND active = 1 ORDER BY created_at DESC LIMIT 20");
                 $stmt->execute([$reviews_config['id']]);
                 $review_items = $stmt->fetchAll();
                 // Add type='review' for the JS loop
                 foreach ($review_items as &$rItem) {
                     $rItem['type'] = 'review';
                 }
             }
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

        // Merge review items into notifications if they are for the toaster
        if (!empty($review_items)) {
            $notifications = array_merge($notifications, $review_items);
        }

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
            'review_config' => $reviews_config,
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

    public function trackReview() {
        $this->addCors();
        $widget_id = $_POST['w'] ?? 0;
        $review_id = $_POST['rid'] ?? 0;
        $type = $_POST['type'] ?? 'unknown';

        if (!$widget_id || !$review_id) return;

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("INSERT INTO review_analytics (review_id, event_type, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$review_id, $type]);
    }

    public function submitReview() {
        $this->addCors();
        $widget_id = $_POST['w'] ?? 0;
        $review_id = $_POST['rid'] ?? 0;

        $rating = $_POST['rating'] ?? 0;
        $name = $_POST['name'] ?? null;
        $email = $_POST['email'] ?? null;
        $feedback = $_POST['feedback'] ?? null;

        if (!$widget_id || !$review_id) {
             echo json_encode(['error' => 'Missing required fields']);
             return;
        }

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("INSERT INTO review_feedbacks (review_id, widget_id, rating, name, email, feedback, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$review_id, $widget_id, $rating, $name, $email, $feedback]);

        echo json_encode(['success' => true]);
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
