</main>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Legacy support function just in case some old JS calls it, though we use Bootstrap toggles now
    function toggleSubmenu(id) {
        var el = document.getElementById(id);
        if(el && typeof bootstrap !== 'undefined') {
            // If it's a bootstrap collapse
            var bsCollapse = new bootstrap.Collapse(el, {toggle: true});
        } else if (el) {
            el.classList.toggle('open');
        }
    }
</script>
</body>
</html>
