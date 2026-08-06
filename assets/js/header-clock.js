/* assets/js/header-clock.js */
document.addEventListener('DOMContentLoaded', () => {
    const liveTimeEl = document.getElementById('live-time');
    const currentDateEl = document.getElementById('current-date');

    function updateClock() {
        const now = new Date();

        // Time format: HH:MM:SS AM/PM
        let hours = now.getHours();
        let minutes = now.getMinutes();
        let seconds = now.getSeconds();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        
        const hoursStr = hours < 10 ? '0' + hours : hours;
        const minutesStr = minutes < 10 ? '0' + minutes : minutes;
        const secondsStr = seconds < 10 ? '0' + seconds : seconds;

        const timeString = `${hoursStr}:${minutesStr}:${secondsStr} ${ampm}`;
        if (liveTimeEl) {
            liveTimeEl.textContent = timeString;
        }

        // Date format: Thursday, 23 Jul 2026
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        const dayName = days[now.getDay()];
        const dateNum = now.getDate();
        const monthName = months[now.getMonth()];
        const year = now.getFullYear();

        const dateString = `${dayName}, ${dateNum} ${monthName} ${year}`;
        if (currentDateEl) {
            currentDateEl.textContent = dateString;
        }
    }

    if (liveTimeEl || currentDateEl) {
        setInterval(updateClock, 1000);
        updateClock();
    }
});
