(function () {
  'use strict';

  const timezone = document.body?.dataset?.timezone || 'America/Lima';

  const clockEl = document.getElementById('clock');
  const todayEl = document.getElementById('today');

  if (clockEl && todayEl) {
    const updateClock = () => {
      const now = new Date();
      const dateFormatter = new Intl.DateTimeFormat('es-PE', {
        timeZone: timezone,
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
      });
      const timeFormatter = new Intl.DateTimeFormat('es-PE', {
        timeZone: timezone,
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
      });

      todayEl.textContent = dateFormatter.format(now);
      clockEl.textContent = `${timeFormatter.format(now)} (${timezone.split('/').pop()})`;
    };

    updateClock();
    setInterval(updateClock, 1000);
  }

  const sidebar = document.getElementById('sidebar');
  const toggleMenu = document.getElementById('btnMenu');

  if (sidebar && toggleMenu) {
    toggleMenu.addEventListener('click', () => {
      const opened = sidebar.classList.toggle('open');
      document.body.classList.toggle('menu-open', opened);
      toggleMenu.setAttribute('aria-expanded', opened ? 'true' : 'false');
    });
  }

  const onlineEl = document.getElementById('online');
  if (onlineEl) {
    const target = Number.parseInt(onlineEl.dataset.target ?? '', 10);
    if (!Number.isNaN(target)) {
      onlineEl.textContent = String(target);
    }
  }

  const calendarContainer = document.getElementById('calendar');
  if (!calendarContainer) {
    return;
  }

  let events = [];
  try {
    events = JSON.parse(calendarContainer.dataset.events ?? '[]');
    if (!Array.isArray(events)) {
      events = [];
    }
  } catch (error) {
    events = [];
  }

  const eventsByDate = events.reduce((acc, event) => {
    if (!event || typeof event.date !== 'string') {
      return acc;
    }
    const isoDate = event.date;
    if (!acc[isoDate]) {
      acc[isoDate] = [];
    }
    acc[isoDate].push(event);
    return acc;
  }, Object.create(null));

  const today = new Date();
  const currentMonth = new Date(today.getFullYear(), today.getMonth(), 1);

  const renderCalendar = (referenceDate) => {
    const monthStart = new Date(referenceDate.getFullYear(), referenceDate.getMonth(), 1);
    const monthEnd = new Date(referenceDate.getFullYear(), referenceDate.getMonth() + 1, 0);

    const startOffset = (monthStart.getDay() + 6) % 7; // lunes = 0
    const totalDays = startOffset + monthEnd.getDate();
    const totalCells = Math.ceil(totalDays / 7) * 7;

    const dayNames = ['L', 'M', 'X', 'J', 'V', 'S', 'D'];

    let html = '<div class="calendar-header">';
    const monthFormatter = new Intl.DateTimeFormat('es-PE', {
      month: 'long',
      year: 'numeric',
    });
    html += `<strong>${monthFormatter.format(referenceDate)}</strong>`;
    html += '<small style="color:var(--muted)">Lun a Dom</small>';
    html += '</div>';

    html += '<div class="calendar-grid">';
    html += dayNames.map((day) => `<div class="calendar-weekday">${day}</div>`).join('');

    for (let cell = 0; cell < totalCells; cell += 1) {
      const dayNumber = cell - startOffset + 1;
      const date = new Date(referenceDate.getFullYear(), referenceDate.getMonth(), dayNumber);
      const isOutside = dayNumber < 1 || dayNumber > monthEnd.getDate();
      const isToday = !isOutside && date.toDateString() === today.toDateString();
      const iso = date.toISOString().slice(0, 10);
      const dayEvents = eventsByDate[iso] ?? [];

      const dayClasses = ['calendar-day'];
      if (isOutside) {
        dayClasses.push('calendar-day--outside');
      }
      if (isToday) {
        dayClasses.push('calendar-day--today');
      }

      html += `<div class="${dayClasses.join(' ')}">`;
      html += `<div class="calendar-day-number">${date.getDate()}</div>`;

      if (!isOutside && dayEvents.length > 0) {
        const statusClass = (status) => {
          if (status === 'confirmada') return 'ok';
          if (status === 'cancelada') return 'danger';
          return 'warn';
        };

        const maxVisible = 2;
        dayEvents.slice(0, maxVisible).forEach((event) => {
          const badge = statusClass(event.estado);
          const icon = badge === 'ok' ? '✔' : badge === 'danger' ? '✖' : '⧗';
          const title = event.paquete ?? 'Salida';
          html += `<div class="calendar-event ${badge}">${icon} ${title}</div>`;
        });

        if (dayEvents.length > maxVisible) {
          html += `<div class="calendar-more">+${dayEvents.length - maxVisible} más</div>`;
        }
      }

      html += '</div>';
    }

    html += '</div>';
    calendarContainer.innerHTML = html;
  };

  renderCalendar(currentMonth);
})();
