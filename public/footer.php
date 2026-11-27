    </main>
    <footer>
        <div class="footer-container">
            <div>
                <strong>University Student Information Management System (UniSIMS)</strong>
                <p>Advanced DBMS Capstone Project &bull; Fully Normalized (3NF) &bull; MySQL / MariaDB</p>
            </div>
            <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                <span>Active Backend: <strong style="color: #6ee7b7;"><?= htmlspecialchars($db_connection_info ?? 'Connected') ?></strong></span> &bull; 
                <span>Tables: <strong>7 Normalized (3NF)</strong></span> &bull; 
                <span style="color: #86efac; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <span style="display:inline-block; width:7px; height:7px; border-radius:50%; background:#22c55e;"></span>
                    Two-Way Real-Time Sync (No Export Needed)
                </span>
            </div>
        </div>
    </footer>
    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</body>
</html>
