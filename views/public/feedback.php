<!DOCTYPE html>
<html lang="km" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:ital,wght@0,300..700;1,300..700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body, html, input, button, select, textarea, .btn {
            font-family: 'Kantumruy Pro', system-ui, -apple-system, sans-serif !important;
        }
        body { background-color: #f8fafc; color: #1e293b; padding-bottom: 50px; }
        .hero-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            color: #fff;
            padding: 2rem 1rem;
            border-radius: 0 0 24px 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }
        .star-rating {
            display: inline-flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 8px;
        }
        .star-rating input {
            display: none;
        }
        .star-rating label {
            font-size: 2.2rem;
            color: #cbd5e1;
            cursor: pointer;
            transition: color 0.15s ease, transform 0.15s ease;
        }
        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label {
            color: #f59e0b;
            transform: scale(1.1);
        }
        .card-rating-box {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        }
    </style>
</head>
<body>
    <div class="hero-banner text-center mb-4">
        <div class="container" style="max-width: 600px;">
            <span class="badge bg-warning text-dark px-3 py-1 rounded-pill mb-2 fw-bold">
                <i class="bi bi-star-fill me-1"></i> ការស្ទង់មតិ & ការវាយតម្លៃ
            </span>
            <h4 class="fw-bold text-white mb-1"><?= htmlspecialchars($workshop['name']) ?></h4>
            <div class="text-white-50 small"><?= htmlspecialchars($workshop['business_name']) ?></div>
        </div>
    </div>

    <div class="container" style="max-width: 600px;">
        <!-- Success State Container (Initially Hidden) -->
        <div id="thankYouCard" class="card card-rating-box text-center p-5 d-none">
            <div class="mb-3 text-success">
                <i class="bi bi-check-circle-fill" style="font-size: 4rem;"></i>
            </div>
            <h3 class="fw-bold mb-2">សូមអរគុណយ៉ាងជ្រាលជ្រៅ!</h3>
            <p class="text-muted mb-4">
                មតិកែលម្អដ៏មានតម្លៃរបស់អ្នកត្រូវបានបញ្ជូនរួចរាល់ហើយ។ ការចូលរួមរបស់អ្នកបានជួយឱ្យយើងរៀបចំកម្មវិធីកាន់តែប្រសើរឡើងនៅពេលក្រោយ!
            </p>
            <div>
                <a href="<?= APP_URL ?>/live/<?= $workshop['id'] ?>/interactive" class="btn btn-outline-primary fw-bold">
                    <i class="bi bi-arrow-left me-1"></i> ត្រឡប់ទៅទំព័រសួរ-ឆ្លើយ
                </a>
            </div>
        </div>

        <!-- Feedback Form -->
        <div id="feedbackFormContainer" class="card card-rating-box p-4 mb-4">
            <p class="text-muted small text-center mb-4">
                សូមចំណាយពេលបន្តិច ដើម្បីផ្ដល់ពិន្ទុ និងមតិកែលម្អលើកម្មវិធីនេះ។ មតិរបស់អ្នកមានសារៈសំខាន់ខ្លាំងណាស់សម្រាប់យើង!
            </p>

            <form id="formFeedback" onsubmit="submitFeedback(event)">
                <!-- 1. Overall Rating -->
                <div class="text-center mb-4 pb-3 border-bottom">
                    <label class="form-label fw-bold d-block mb-1 fs-5 text-dark">
                        ១. តើអ្នកពេញចិត្តលើកម្មវិធីនេះកម្រិតណា? <span class="text-danger">*</span>
                    </label>
                    <div class="star-rating my-2">
                        <input type="radio" id="star5" name="overall_rating" value="5" required>
                        <label for="star5" title="ល្អឥតខ្ចោះ (5/5)">★</label>
                        <input type="radio" id="star4" name="overall_rating" value="4">
                        <label for="star4" title="ល្អណាស់ (4/5)">★</label>
                        <input type="radio" id="star3" name="overall_rating" value="3">
                        <label for="star3" title="ល្អបង្គួរ (3/5)">★</label>
                        <input type="radio" id="star2" name="overall_rating" value="2">
                        <label for="star2" title="មធ្យម (2/5)">★</label>
                        <input type="radio" id="star1" name="overall_rating" value="1">
                        <label for="star1" title="ត្រូវកែលម្អ (1/5)">★</label>
                    </div>
                    <div class="small text-muted" id="ratingText">សូមចុចលើផ្កាយដើម្បីផ្ដល់ពិន្ទុ (1 ដល់ 5)</div>
                </div>

                <!-- 2. Sub-ratings (Speaker & Content) -->
                <div class="row g-3 mb-4 pb-3 border-bottom">
                    <div class="col-sm-6 text-center">
                        <label class="form-label small fw-bold text-dark d-block mb-1">
                            ២. ការបកស្រាយរបស់វាគ្មិន
                        </label>
                        <select id="speaker_rating" class="form-select form-select-sm text-center">
                            <option value="5">⭐⭐⭐⭐⭐ ល្អឥតខ្ចោះ (5)</option>
                            <option value="4" selected>⭐⭐⭐⭐ ល្អណាស់ (4)</option>
                            <option value="3">⭐⭐⭐ ល្អបង្គួរ (3)</option>
                            <option value="2">⭐⭐ មធ្យម (2)</option>
                            <option value="1">⭐ ត្រូវកែលម្អ (1)</option>
                        </select>
                    </div>
                    <div class="col-sm-6 text-center">
                        <label class="form-label small fw-bold text-dark d-block mb-1">
                            ៣. ខ្លឹមសារ & ការរៀបចំ
                        </label>
                        <select id="content_rating" class="form-select form-select-sm text-center">
                            <option value="5">⭐⭐⭐⭐⭐ ល្អឥតខ្ចោះ (5)</option>
                            <option value="4" selected>⭐⭐⭐⭐ ល្អណាស់ (4)</option>
                            <option value="3">⭐⭐⭐ ល្អបង្គួរ (3)</option>
                            <option value="2">⭐⭐ មធ្យម (2)</option>
                            <option value="1">⭐ ត្រូវកែលម្អ (1)</option>
                        </select>
                    </div>
                </div>

                <!-- 3. Comments -->
                <div class="mb-4">
                    <label class="form-label fw-bold small text-dark mb-1">
                        ៤. មតិកែលម្អ ឬសំណូមពរបន្ថែម
                    </label>
                    <textarea id="comments" class="form-control" rows="3" 
                              placeholder="តើលោកអ្នកពេញចិត្តអ្វីខ្លះ? ឬមានអ្វីដែលគួរកែលម្អបន្ថែមនៅពេលក្រោយ?"></textarea>
                </div>

                <!-- 4. Attendee info -->
                <div class="mb-4">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <label class="form-label small fw-bold text-dark mb-1">ឈ្មោះរបស់អ្នក (ស្រេចចិត្ត)</label>
                            <input type="text" id="participant_name" class="form-control form-control-sm" 
                                   placeholder="ឈ្មោះ ឬទុកនៅទទេ" 
                                   value="<?= htmlspecialchars($currentUser['name'] ?? '') ?>">
                        </div>
                        <div class="col-auto pt-4">
                            <div class="form-check form-check-inline m-0">
                                <input class="form-check-input" type="checkbox" id="is_anonymous" onchange="toggleAnon()">
                                <label class="form-check-label small" for="is_anonymous">មិនបញ្ចេញឈ្មោះ</label>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" id="btnSubmitFeedback" class="btn btn-warning w-100 fw-bold py-2 rounded-3 text-dark shadow-sm">
                    <i class="bi bi-send-fill me-1"></i> បញ្ជូនការវាយតម្លៃ
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const WORKSHOP_ID = <?= (int)$workshop['id'] ?>;
        const APP_URL = "<?= APP_URL ?>";

        function toggleAnon() {
            const isAnon = document.getElementById('is_anonymous').checked;
            const input = document.getElementById('participant_name');
            if (isAnon) {
                input.dataset.prev = input.value;
                input.value = 'មិនបញ្ចេញឈ្មោះ';
                input.disabled = true;
            } else {
                input.disabled = false;
                input.value = input.dataset.prev || '';
            }
        }

        const ratingDescriptions = {
            '5': '⭐ ល្អឥតខ្ចោះ (5 ផ្កាយ)',
            '4': '⭐ ល្អណាស់ (4 ផ្កាយ)',
            '3': '⭐ ល្អបង្គួរ (3 ផ្កាយ)',
            '2': '⭐ មធ្យម (2 ផ្កាយ)',
            '1': '⭐ ត្រូវកែលម្អ (1 ផ្កាយ)'
        };

        document.querySelectorAll('input[name="overall_rating"]').forEach(radio => {
            radio.addEventListener('change', () => {
                document.getElementById('ratingText').innerText = ratingDescriptions[radio.value] || '';
            });
        });

        function submitFeedback(e) {
            e.preventDefault();
            const ratingRadio = document.querySelector('input[name="overall_rating"]:checked');
            if (!ratingRadio) {
                alert('សូមជ្រើសរើសពិន្ទុផ្កាយ');
                return;
            }

            const overallRating = parseFloat(ratingRadio.value);
            const speakerRating = parseInt(document.getElementById('speaker_rating').value);
            const contentRating = parseInt(document.getElementById('content_rating').value);
            const comments = document.getElementById('comments').value.trim();
            const isAnon = document.getElementById('is_anonymous').checked ? 1 : 0;
            const author = isAnon ? 'មិនបញ្ចេញឈ្មោះ' : document.getElementById('participant_name').value.trim();

            const btn = document.getElementById('btnSubmitFeedback');
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> កំពុងបញ្ជូន...`;

            const formData = new FormData();
            formData.append('workshop_id', WORKSHOP_ID);
            formData.append('overall_rating', overallRating);
            formData.append('speaker_rating', speakerRating);
            formData.append('content_rating', contentRating);
            formData.append('comments', comments);
            formData.append('is_anonymous', isAnon);
            formData.append('participant_name', author);

            fetch(`${APP_URL}/api/feedback/submit`, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-send-fill me-1"></i> បញ្ជូនការវាយតម្លៃ`;
                if (res.success) {
                    document.getElementById('feedbackFormContainer').classList.add('d-none');
                    document.getElementById('thankYouCard').classList.remove('d-none');
                } else {
                    alert(res.message || 'បរាជ័យក្នុងការបញ្ជូន');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-send-fill me-1"></i> បញ្ជូនការវាយតម្លៃ`;
                alert('មានបញ្ហាក្នុងការតភ្ជាប់អ៊ីនធឺណិត');
            });
        }
    </script>
</body>
</html>
