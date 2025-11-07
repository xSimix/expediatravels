      </section>
    </main>
  </div>
  <script src="recursos/dashboard.js"></script>
  <?php if (!empty($scriptsProcesados ?? [])): ?>
    <?php foreach ($scriptsProcesados as $script): ?>
      <?= $script . "\n"; ?>
    <?php endforeach; ?>
  <?php endif; ?>
</body>
</html>
