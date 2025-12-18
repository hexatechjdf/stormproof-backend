/**
 * Calendar Utilities - Timezone and Date Management
 * Handles timezone detection, conversion, and calendar operations
 */

class TimezoneManager {
    constructor() {
        this.currentTimezone = this.detectTimezone();
        this.timezones = this.getTimezoneList();
    }

    /**
     * Auto-detect user's timezone
     */
    detectTimezone() {
        return Intl.DateTimeFormat().resolvedOptions().timeZone;
    }

    /**
     * Get comprehensive timezone list
     */
    getTimezoneList() {
        return {
            'Americas': {
                'America/New_York': 'Eastern Time (ET)',
                'America/Chicago': 'Central Time (CT)',
                'America/Denver': 'Mountain Time (MT)',
                'America/Los_Angeles': 'Pacific Time (PT)',
                'America/Anchorage': 'Alaska Time (AKT)',
                'Pacific/Honolulu': 'Hawaii-Aleutian Time (HST)',
                'America/Toronto': 'Eastern Time (Canada)',
                'America/Mexico_City': 'Central Time (Mexico)',
                'America/Sao_Paulo': 'Brasília Time (BRT)',
                'America/Buenos_Aires': 'Argentina Time (ART)',
            },
            'Europe': {
                'Europe/London': 'Greenwich Mean Time (GMT)',
                'Europe/Paris': 'Central European Time (CET)',
                'Europe/Berlin': 'Central European Time (CET)',
                'Europe/Madrid': 'Central European Time (CET)',
                'Europe/Rome': 'Central European Time (CET)',
                'Europe/Amsterdam': 'Central European Time (CET)',
                'Europe/Brussels': 'Central European Time (CET)',
                'Europe/Vienna': 'Central European Time (CET)',
                'Europe/Prague': 'Central European Time (CET)',
                'Europe/Warsaw': 'Central European Time (CET)',
                'Europe/Moscow': 'Moscow Standard Time (MSK)',
                'Europe/Istanbul': 'Eastern European Time (EET)',
                'Europe/Athens': 'Eastern European Time (EET)',
                'Europe/Helsinki': 'Eastern European Time (EET)',
            },
            'Asia': {
                'Asia/Dubai': 'Gulf Standard Time (GST)',
                'Asia/Kolkata': 'Indian Standard Time (IST)',
                'Asia/Bangkok': 'Indochina Time (ICT)',
                'Asia/Singapore': 'Singapore Time (SGT)',
                'Asia/Hong_Kong': 'Hong Kong Time (HKT)',
                'Asia/Shanghai': 'China Standard Time (CST)',
                'Asia/Tokyo': 'Japan Standard Time (JST)',
                'Asia/Seoul': 'Korea Standard Time (KST)',
                'Asia/Manila': 'Philippine Time (PHT)',
                'Asia/Jakarta': 'Western Indonesia Time (WIB)',
                'Asia/Karachi': 'Pakistan Standard Time (PKT)',
                'Asia/Tehran': 'Iran Standard Time (IRST)',
                'Asia/Jerusalem': 'Israel Standard Time (IST)',
            },
            'Africa': {
                'Africa/Cairo': 'Eastern European Time (EET)',
                'Africa/Johannesburg': 'South Africa Standard Time (SAST)',
                'Africa/Lagos': 'West Africa Time (WAT)',
                'Africa/Nairobi': 'East Africa Time (EAT)',
                'Africa/Casablanca': 'Western European Time (WET)',
            },
            'Oceania': {
                'Australia/Sydney': 'Australian Eastern Time (AEDT/AEST)',
                'Australia/Melbourne': 'Australian Eastern Time (AEDT/AEST)',
                'Australia/Brisbane': 'Australian Eastern Time (AEST)',
                'Australia/Perth': 'Australian Western Time (AWST)',
                'Australia/Adelaide': 'Australian Central Time (ACDT/ACST)',
                'Pacific/Auckland': 'New Zealand Standard Time (NZST)',
                'Pacific/Fiji': 'Fiji Time (FJT)',
            },
        };
    }

    /**
     * Convert date to specific timezone
     */
    convertToTimezone(date, timezone) {
        const formatter = new Intl.DateTimeFormat('en-US', {
            timeZone: timezone,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
        });

        const parts = formatter.formatToParts(date);
        const result = {};

        parts.forEach(({ type, value }) => {
            result[type] = value;
        });

        return new Date(
            `${result.year}-${result.month}-${result.day}T${result.hour}:${result.minute}:${result.second}`
        );
    }

    /**
     * Get timezone offset
     */
    getTimezoneOffset(timezone) {
        const now = new Date();
        const utcDate = new Date(now.toLocaleString('en-US', { timeZone: 'UTC' }));
        const tzDate = new Date(now.toLocaleString('en-US', { timeZone: timezone }));
        return (tzDate - utcDate) / (1000 * 60 * 60);
    }

    /**
     * Format date in specific timezone
     */
    formatInTimezone(date, timezone, format = 'long') {
        const options = {
            timeZone: timezone,
            year: 'numeric',
            month: format === 'short' ? 'short' : 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        };

        return new Intl.DateTimeFormat('en-US', options).format(date);
    }

    /**
     * Get current time in timezone
     */
    getCurrentTimeInTimezone(timezone) {
        return this.formatInTimezone(new Date(), timezone);
    }
}

/**
 * Calendar Event Manager
 */
class CalendarEventManager {
    constructor(calendarElement, config = {}) {
        this.calendarElement = calendarElement;
        this.config = {
            maxSelections: 3,
            businessHours: {
                start: '08:00',
                end: '18:00',
                daysOfWeek: [1, 2, 3, 4, 5], // Monday to Friday
            },
            ...config,
        };

        this.selectedDates = [];
        this.events = [];
        this.timezoneManager = new TimezoneManager();
    }

    /**
     * Initialize calendar with FullCalendar
     */
    initializeCalendar() {
        const self = this;

        this.calendar = new FullCalendar.Calendar(this.calendarElement, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: '',
            },
            height: 'auto',
            contentHeight: 'auto',
            editable: false,
            selectable: true,
            selectConstraint: 'businessHours',
            businessHours: {
                daysOfWeek: this.config.businessHours.daysOfWeek,
                startTime: this.config.businessHours.start,
                endTime: this.config.businessHours.end,
            },
            dateClick: (info) => this.handleDateClick(info),
            eventClick: (info) => this.handleEventClick(info),
            events: (info, successCallback, failureCallback) => {
                this.loadEvents(successCallback, failureCallback);
            },
            eventDidMount: (info) => this.styleEvent(info),
        });

        this.calendar.render();
        return this.calendar;
    }

    /**
     * Handle date click
     */
    handleDateClick(info) {
        if (this.selectedDates.length >= this.config.maxSelections) {
            this.showNotification(
                `Maximum ${this.config.maxSelections} dates allowed`,
                'warning'
            );
            return;
        }

        const dateStr = info.dateStr;

        // Check if already selected
        if (this.selectedDates.some(d => d.date === dateStr)) {
            this.removeSelectedDate(dateStr);
            return;
        }

        // Check if date is in the past
        if (new Date(dateStr) < new Date()) {
            this.showNotification('Cannot select past dates', 'error');
            return;
        }

        // Check if date is a business day
        if (!this.isBusinessDay(new Date(dateStr))) {
            this.showNotification('Please select a business day (Mon-Fri)', 'warning');
            return;
        }

        // Add selected date
        this.addSelectedDate(dateStr);

        // Trigger callback
        if (this.config.onDateSelect) {
            this.config.onDateSelect(dateStr);
        }
    }

    /**
     * Handle event click
     */
    handleEventClick(info) {
        const event = info.event;
        const details = {
            id: event.id,
            title: event.title,
            start: event.start,
            end: event.end,
            extendedProps: event.extendedProps,
        };

        if (this.config.onEventClick) {
            this.config.onEventClick(details);
        }
    }

    /**
     * Add selected date
     */
    addSelectedDate(dateStr) {
        const dateObj = new Date(dateStr);
        this.selectedDates.push({
            date: dateStr,
            datetime: dateObj.toISOString(),
            timestamp: dateObj.getTime(),
        });

        this.updateCalendarDisplay();
    }

    /**
     * Remove selected date
     */
    removeSelectedDate(dateStr) {
        this.selectedDates = this.selectedDates.filter(d => d.date !== dateStr);
        this.updateCalendarDisplay();
    }

    /**
     * Update calendar display with selected dates
     */
    updateCalendarDisplay() {
        // Add visual indicators for selected dates
        this.selectedDates.forEach(item => {
            const dateEl = document.querySelector(`[data-date="${item.date}"]`);
            if (dateEl) {
                dateEl.classList.add('selected-date');
            }
        });

        if (this.config.onSelectionChange) {
            this.config.onSelectionChange(this.selectedDates);
        }
    }

    /**
     * Check if date is a business day
     */
    isBusinessDay(date) {
        const dayOfWeek = date.getDay();
        return this.config.businessHours.daysOfWeek.includes(dayOfWeek);
    }

    /**
     * Load events from API
     */
    loadEvents(successCallback, failureCallback) {
        const inspectionId = this.config.inspectionId;

        fetch(`/api/inspections/${inspectionId}/appointments`)
            .then(response => response.json())
            .then(data => {
                this.events = data.appointments.map(apt => ({
                    id: apt.id,
                    title: apt.title,
                    start: apt.start_time,
                    end: apt.end_time,
                    className: `status-${apt.status}`,
                    extendedProps: {
                        status: apt.status,
                        type: apt.type,
                    },
                }));
                successCallback(this.events);
            })
            .catch(error => {
                console.error('Error loading events:', error);
                failureCallback(error);
            });
    }

    /**
     * Style event based on status
     */
    styleEvent(info) {
        const event = info.event;
        const status = event.extendedProps.status;

        const colors = {
            pending: '#ffc107',
            confirmed: '#28a745',
            completed: '#17a2b8',
            cancelled: '#dc3545',
            no_show: '#6c757d',
        };

        info.el.style.backgroundColor = colors[status] || '#007bff';
        info.el.style.borderColor = colors[status] || '#007bff';
    }

    /**
     * Change calendar view
     */
    changeView(viewName) {
        if (this.calendar) {
            this.calendar.changeView(viewName);
        }
    }

    /**
     * Get selected dates
     */
    getSelectedDates() {
        return this.selectedDates;
    }

    /**
     * Clear selected dates
     */
    clearSelectedDates() {
        this.selectedDates = [];
        this.updateCalendarDisplay();
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        // Implement your notification system here
        console.log(`[${type.toUpperCase()}] ${message}`);

        if (this.config.onNotification) {
            this.config.onNotification(message, type);
        }
    }

    /**
     * Refresh calendar
     */
    refresh() {
        if (this.calendar) {
            this.calendar.refetchEvents();
        }
    }
}

/**
 * Form Manager for appointment details
 */
class AppointmentFormManager {
    constructor(formElement, config = {}) {
        this.form = formElement;
        this.config = config;
        this.formData = {};
    }

    /**
     * Initialize form
     */
    initialize() {
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        this.attachFieldListeners();
    }

    /**
     * Attach listeners to form fields
     */
    attachFieldListeners() {
        const fields = this.form.querySelectorAll('[name]');
        fields.forEach(field => {
            field.addEventListener('change', (e) => {
                this.formData[field.name] = field.value;
            });
        });
    }

    /**
     * Handle form submission
     */
    handleSubmit(e) {
        e.preventDefault();

        const formData = new FormData(this.form);
        const data = Object.fromEntries(formData);

        if (this.config.onSubmit) {
            this.config.onSubmit(data);
        }
    }

    /**
     * Populate form with data
     */
    populateForm(data) {
        Object.keys(data).forEach(key => {
            const field = this.form.elements[key];
            if (field) {
                field.value = data[key];
            }
        });
    }

    /**
     * Get form data
     */
    getFormData() {
        return new FormData(this.form);
    }

    /**
     * Validate form
     */
    validate() {
        return this.form.checkValidity();
    }

    /**
     * Reset form
     */
    reset() {
        this.form.reset();
        this.formData = {};
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        TimezoneManager,
        CalendarEventManager,
        AppointmentFormManager,
    };
}