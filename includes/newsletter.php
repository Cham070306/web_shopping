<section class="newsletter">
    <img src="/web_shopping/assets/images/image.png" style="
       position:absolute;
       top:0;
       left:50%;
       transform:translateX(-50%);   
       height:100%;
       width:auto;
       min-width:100%;
       z-index:1;
       pointer-events:none;
    " onerror="this.style.display='none'">
    <div class="newsletter-inner">
        <h2>Join Our Newsletter</h2>
        <p>Sign up for deals, new products and promotions</p>
        <form class="newsletter-form" id="newsletterForm">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9BA3AF" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
            </svg>
            <input type="email" id="newsletterEmail" placeholder="Email address" aria-label="Email address" required>
            <button type="submit" id="newsletterBtn">Signup</button>
        </form>
        <p id="newsletterMsg" style="margin-top:14px; font-size:14px; display:none;"></p>
    </div>
</section>

<script>
document.getElementById('newsletterForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const email = document.getElementById('newsletterEmail').value.trim();
    const btn   = document.getElementById('newsletterBtn');
    const msg   = document.getElementById('newsletterMsg');

    if (!email) return;

    btn.textContent = 'Sending...';
    btn.disabled = true;

    const formData = new FormData();
    formData.append('email', email);

    fetch('/web_shopping/controllers/NewsletterController.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        msg.style.display = 'block';
        if (data.success) {
            msg.style.color = '#38CB89';
            msg.textContent = '✓ ' + data.message;
            document.getElementById('newsletterEmail').value = '';
        } else {
            msg.style.color = '#E03';
            msg.textContent = '✕ ' + data.message;
        }
        btn.textContent = 'Signup';
        btn.disabled = false;
    })
    .catch(() => {
        msg.style.display = 'block';
        msg.style.color   = '#E03';
        msg.textContent   = '✕ Network error. Please try again.';
        btn.textContent   = 'Signup';
        btn.disabled      = false;
    });
});
</script>
