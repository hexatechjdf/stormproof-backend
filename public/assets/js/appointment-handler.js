/**
 * Appointment Handler - Multi-date Selection and Form Processing
 * Manages the complete appointment booking workflow
 */

class AppointmentHandler {
    constructor(config = {}) {
        this.config = {
            maxSelections: 3,
            inspectionId: config.inspectionId || null,
            formSelector: '#inspection-form',
            calendarSelector: '#calendar',
            panelSelector: '#form-slide-panel',
            overlaySelector: '#form-panel-overlay',
            selectedListSelector: '#selected-dates-list',
            selectedCountSelector: '#selected-count',
            ...config,
        };

        this.selectedDates = [];
        this.currentSelectedDate = null;
        this.timezoneManager = new TimezoneManager();
        this.calendar = null;
        this.isSubmitting = false;

        this.init();
    }

    /**
     * Initialize the appointment handler
     */
    init() {
        this.setupCalendar();
        this.setupFormPanel();
        this.setupEventListeners();
        this.loadExistingAppointments();
    }

    /**
     * Setup FullCalendar
     */
    setupCalendar() {
        const calendarEl = document.querySelector(this.config.calendarSelector);
        if (!calendarEl) return;

        this.calendar = new FullCalendar.Calendar(calendarEl, {
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
                daysOfWeek: [1, 2, 3, 4, 5],
                startTime: '08:00',
                endTime: '18:00',
            },
            dateClick: (info) => this.handleDateClick(info),
            eventClick: (info) => this.handleEventClick(info),
            events: (info, successCallback, failureCallback) => {
                this.loadCalendarEvents(successCallback, failureCallback);
            },
            eventDidMount: (info) => this.styleEventElement(info),
        });

        this.calendar.render();
    }

    /**
     * Setup form panel
     */
    setupFormPanel() {
        const panel = document.querySelector(this.config.panelSelector);
        const overlay = document.querySelector(this.config.overlaySelector);
        const closeBtn = panel?.querySelector('#close-panel');
        const cancelBtn = document.querySelector('#cancel-form');

        if (closeBtn) closeBtn.addEventListener('click', () => this.closePanel());
        if (overlay) overlay.addEventListener('click', () => this.closePanel());
        if (cancelBtn) cancelBtn.addEventListener('click', () => this.closePanel());

        // Access instructions toggle
        const accessCheckbox = document.getElementById('access-instructions');
        if (accessCheckbox) {
            accessCheckbox.addEventListener('change', (e) => {
                const field = document.getElementById('access-instructions-field');
                if (field) {
                    field.style.display = e.target.checked ? 'block' : 'none';
                }
            });
        }
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Timezone selector
        const tzSelect = document.getElementById('timezone-select');
        if (tzSelect) {
            tzSelect.addEventListener('change', (e) => {
                this.handleTimezoneChange(e.target.value);
            });
        }

        // View toggle buttons
        document.querySelectorAll('.view-toggle').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const view = e.target.dataset.view;
                this.changeCalendarView(view, e.target);
            });
        });

        // Form submission
        const form = document.querySelector(this.config.formSelector);
        if (form) {
            form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        }

        // Contact method change
        const contactMethod = document.getElementById('contact-method');
        if (contactMethod) {
            contactMethod.addEventListener('change', (e) => {
                this.updateContactFields(e.target.value);
            });
        }
    }

    /**
     * Handle date click on calendar
     */
    handleDateClick(info) {
        const dateStr = info.dateStr;
        const dateObj = new Date(dateStr);

        // Validate date
        if (!this.isValidDate(dateObj)) {
            this.showNotification('Cannot select past dates or weekends', 'warning');
            return;
        }

        // Check if already selected
        if (this.selectedDates.some(d => d.date === dateStr)) {
            this.removeSelectedDate(dateStr);
            return;
        }

        // Check max selections
        if (this.selectedDates.length >= this.config.maxSelections) {
            this.showNotification(
                `Maximum ${this.config.maxSelections} dates allowed`,
                'warning'
            );
            return;
        }

        // Store current selection and open form
        this.currentSelectedDate = dateStr;
        this.openPanel();
    }

    /**
     * Handle event click (existing appointments)
     */
    handleEventClick(info) {
        const event = info.event;
        const status = event.extendedProps.status;

        let message = `${event.title}\n`;
        message += `Time: ${event.start.toLocaleString()}\n`;
        message += `Status: ${status}`;

        if (event.extendedProps.notes) {
            message += `\nNotes: ${event.extendedProps.notes}`;
        }

        this.showNotification(message, 'info');
    }

    /**
     * Validate date
     */
    isValidDate(date) {
        const now = new Date();
        now.setHours(0, 0, 0, 0);

        // Check if past
        if (date < now) return false;

        // Check if weekend
        const dayOfWeek = date.getDay();
        if (dayOfWeek === 0 || dayOfWeek === 6) return false;

        return true;
    }

    /**
     * Add selected date
     */
    addSelectedDate(dateStr, time = '09:00', formData = {}) {
        if (this.selectedDates.some(d => d.date === dateStr)) {
            return;
        }

        const datetime = `${dateStr}T${time}`;
        const dateObj = new Date(datetime);

        this.selectedDates.push({
            id: `sel_${Date.now()}_${Math.random()}`,
            date: dateStr,
            datetime: datetime,
            time: time,
            inspectionType: formData.inspection_type || '',
            contactMethod: formData.contact_method || '',
            notes: formData.notes || '',
        });

        this.updateSelectedDatesList();
        this.updateSelectedCount();
    }

    /**
     * Remove selected date
     */
    removeSelectedDate(dateStr) {
        this.selectedDates = this.selectedDates.filter(d => d.date !== dateStr);
        this.updateSelectedDatesList();
        this.updateSelectedCount();
    }

    /**
     * Update selected dates list display
     */
    updateSelectedDatesList() {
        const listEl = document.querySelector(this.config.selectedListSelector);
        if (!listEl) return;

        if (this.selectedDates.length === 0) {
            listEl.innerHTML = '<p class="text-muted">No dates selected yet</p>';
            return;
        }

        const html = this.selectedDates.map(item => {
            const dateObj = new Date(item.datetime);
            const formatted = dateObj.toLocaleString('en-US', {
                weekday: 'short',
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });

            return `
                <div class="date-badge">
                    <div class="date-badge-content">
                        <div class="date-badge-datetime">${formatted}</div>
                        ${item.inspectionType ? `<div class="date-badge-type">${item.inspectionType}</div>` : ''}
                    </div>
                    <button type="button" class="date-badge-remove" onclick="appointmentHandler.removeSelectedDate('${item.date}')">
                        ×
                    </button>
                </div>
            `;
        }).join('');

        listEl.innerHTML = html;
    }

    /**
     * Update selected count
     */
    updateSelectedCount() {
        const countEl = document.querySelector(this.config.selectedCountSelector);
        if (countEl) {
            countEl.textContent = this.selectedDates.length;
        }
    }

    /**
     * Open form panel
     */
    openPanel() {
        const panel = document.querySelector(this.config.panelSelector);
        const overlay = document.querySelector(this.config.overlaySelector);

        if (panel) panel.classList.add('active');
        if (overlay) overlay.classList.add('active');
        document.body.style.overflow = 'hidden';

        // Set the selected datetime in the form
        if (this.currentSelectedDate) {
            const datetimeInput = document.getElementById('selected-datetime');
            if (datetimeInput) {
                datetimeInput.value = `${this.currentSelectedDate}T09:00`;
            }
        }
    }

    /**
     * Close form panel
     */
    closePanel() {
        const panel = document.querySelector(this.config.panelSelector);
        const overlay = document.querySelector(this.config.overlaySelector);

        if (panel) panel.classList.remove('active');
        if (overlay) overlay.classList.remove('active');
        document.body.style.overflow = 'auto';

        this.currentSelectedDate = null;
    }

    /**
     * Update contact fields visibility based on method
     */
    updateContactFields(method) {
        const phoneField = document.getElementById('contact-number');
        const emailField = document.getElementById('contact-email');

        if (phoneField) {
            phoneField.required = ['phone', 'sms', 'whatsapp'].includes(method);
        }

        if (emailField) {
            emailField.required = method === 'email';
        }
    }

    /**
     * Handle form submission
     */
    handleFormSubmit(e) {
        e.preventDefault();

        if (this.isSubmitting) return;

        // Get form data
        const form = document.querySelector(this.config.formSelector);
        const formData = new FormData(form);

        // Validate form
        if (!form.checkValidity()) {
            this.showNotification('Please fill in all required fields', 'error');
            return;
        }

        // Add current selected date
        if (this.currentSelectedDate) {
            const datetime = document.getElementById('selected-datetime').value;
            this.addSelectedDate(
                this.currentSelectedDate,
                datetime.split('T')[1],
                Object.fromEntries(formData)
            );
        }

        // Check if dates are selected
        if (this.selectedDates.length === 0) {
            this.showNotification('Please select at least one date', 'error');
            return;
        }

        // Submit
        this.submitAppointments(formData);
    }

    /**
     * Submit appointments
     */
    submitAppointments(formData) {
        this.isSubmitting = true;

        // Add all selected dates to form
        const form = document.querySelector(this.config.formSelector);
        const preferredDatesInput = document.getElementById('selected-dates-input');

        // Clear existing hidden inputs
        form.querySelectorAll('input[name="preferred_dates[]"]').forEach(el => el.remove());

        // Add selected dates
        this.selectedDates.forEach(item => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'preferred_dates[]';
            input.value = item.datetime;
            form.appendChild(input);
        });

        // Submit form
        const submitData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: submitData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showNotification('Appointment request submitted successfully!', 'success');
                this.resetForm();
                this.closePanel();
                setTimeout(() => {
                    if (this.calendar) this.calendar.refetchEvents();
                }, 1000);
            } else {
                this.showNotification(data.message || 'Failed to submit request', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showNotification('An error occurred while submitting your request', 'error');
        })
        .finally(() => {
            this.isSubmitting = false;
        });
    }

    /**
     * Reset form
     */
    resetForm() {
        const form = document.querySelector(this.config.formSelector);
        if (form) form.reset();

        this.selectedDates = [];
        this.currentSelectedDate = null;
        this.updateSelectedDatesList();
        this.updateSelectedCount();
    }

    /**
     * Load calendar events
     */
    loadCalendarEvents(successCallback, failureCallback) {
        if (!this.config.inspectionId) {
            successCallback([]);
            return;
        }

        fetch(`/api/inspections/${this.config.inspectionId}/appointments`)
            .then(response => response.json())
            .then(data => {
                const events = (data.appointments || []).map(apt => ({
                    id: apt.id,
                    title: apt.title || 'Booked Appointment',
                    start: apt.start_time,
                    end: apt.end_time,
                    className: `status-${apt.status}`,
                    extendedProps: {
                        status: apt.status,
                        type: apt.type,
                        notes: apt.notes || '',
                    },
                }));
                successCallback(events);
            })
            .catch(error => {
                console.error('Error loading events:', error);
                failureCallback(error);
            });
    }

    /**
     * Style event element
     */
    styleEventElement(info) {
        const event = info.event;
        const status = event.extendedProps.status;

        const colors = {
            pending: '#ffc107',
            confirmed: '#28a745',
            completed: '#17a2b8',
            cancelled: '#dc3545',
            no_show: '#6c757d',
        };

        const color = colors[status] || '#007bff';
        info.el.style.backgroundColor = color;
        info.el.style.borderColor = color;
    }

    /**
     * Load existing appointments
     */
    loadExistingAppointments() {
        if (this.calendar) {
            this.calendar.refetchEvents();
        }
    }

    /**
     * Handle timezone change
     */
    handleTimezoneChange(timezone) {
        this.timezoneManager.currentTimezone = timezone;

        // Update all date displays
        this.updateSelectedDatesList();

        // Optionally reload calendar with new timezone
        if (this.calendar) {
            this.calendar.refetchEvents();
        }

        this.showNotification(`Timezone changed to ${timezone}`, 'info');
    }

    /**
     * Change calendar view
     */
    changeCalendarView(viewName, buttonEl) {
        if (!this.calendar) return;

        // Update active button
        document.querySelectorAll('.view-toggle').forEach(btn => {
            btn.classList.remove('active');
        });
        if (buttonEl) buttonEl.classList.add('active');

        // Change view
        this.calendar.changeView(viewName);
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        // Create a simple notification (you can replace with your notification library)
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info',
        }[type] || 'alert-info';

        const alertEl = document.createElement('div');
        alertEl.className = `alert ${alertClass} alert-dismissible fade show`;
        alertEl.role = 'alert';
        alertEl.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // Insert at top of page
        const container = document.querySelector('.inspection-scheduler');
        if (container) {
            container.insertBefore(alertEl, container.firstChild);

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                alertEl.remove();
            }, 5000);
        }
    }

    /**
     * Get selected dates
     */
    getSelectedDates() {
        return this.selectedDates;
    }

    /**
     * Get selected dates as array of datetime strings
     */
    getSelectedDatetimes() {
        return this.selectedDates.map(d => d.datetime);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.appointmentHandler = new AppointmentHandler({
        inspectionId: document.querySelector('[data-inspection-id]')?.dataset.inspectionId || null,
    });
});