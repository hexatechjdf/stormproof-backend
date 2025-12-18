@extends('layouts.homeowner')
@section('title', 'Schedule Inspection')
@section('content')
    <div class="inspection-scheduler">
        <!-- Header Section -->
        <div class="scheduler-header">
            <div class="header-content">
                <h1>Schedule Your Inspection</h1>
                <p class="subtitle">Select up to 3 preferred dates and times for your inspection</p>
            </div>

            <!-- Timezone and Filter Controls -->
            <div class="controls-section">
                <div class="timezone-control">
                    <label for="timezone-select" class="control-label">Timezone</label>
                    <select id="timezone-select" class="form-control timezone-selector">
                        <option value="auto">Auto-detect ({{ $userTimezone ?? 'UTC' }})</option>
                        <optgroup label="Americas">
                            <option value="America/New_York">Eastern Time (ET)</option>
                            <option value="America/Chicago">Central Time (CT)</option>
                            <option value="America/Denver">Mountain Time (MT)</option>
                            <option value="America/Los_Angeles">Pacific Time (PT)</option>
                        </optgroup>
                        <optgroup label="Europe">
                            <option value="Europe/London">London (GMT)</option>
                            <option value="Europe/Paris">Central European Time (CET)</option>
                            <option value="Europe/Moscow">Moscow (MSK)</option>
                        </optgroup>
                        <optgroup label="Asia">
                            <option value="Asia/Dubai">Dubai (GST)</option>
                            <option value="Asia/Kolkata">India (IST)</option>
                            <option value="Asia/Bangkok">Bangkok (ICT)</option>
                            <option value="Asia/Singapore">Singapore (SGT)</option>
                            <option value="Asia/Hong_Kong">Hong Kong (HKT)</option>
                            <option value="Asia/Tokyo">Tokyo (JST)</option>
                            <option value="Asia/Sydney">Sydney (AEDT)</option>
                        </optgroup>
                    </select>
                </div>

                <div class="view-control">
                    <label class="control-label">View</label>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary view-toggle" data-view="dayGridMonth">Month</button>
                        <button type="button" class="btn btn-sm btn-outline-primary view-toggle" data-view="timeGridWeek">Week</button>
                        <button type="button" class="btn btn-sm btn-outline-primary view-toggle" data-view="timeGridDay">Day</button>
                    </div>
                </div>

                <div class="selected-dates-info">
                    <span class="badge badge-info">Selected: <span id="selected-count">0</span>/3</span>
                </div>
            </div>
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Validation Errors:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Main Calendar Container -->
        <div class="calendar-wrapper">
            <div id="calendar" class="full-calendar"></div>
        </div>

        <!-- Selected Dates Display -->
        <div class="selected-dates-panel">
            <h5>Selected Dates & Times</h5>
            <div id="selected-dates-list" class="selected-list">
                <p class="text-muted">No dates selected yet</p>
            </div>
        </div>
    </div>

    <!-- Slide-out Form Panel -->
    <div class="form-slide-panel" id="form-slide-panel">
        <div class="panel-header">
            <h4>Inspection Details</h4>
            <button type="button" class="btn-close" id="close-panel"></button>
        </div>

        <div class="panel-body">
            <form id="inspection-form" method="POST" action="{{ route('homeowner.inspections.schedule.store', $inspection->id) }}">
                @csrf

                <!-- Hidden field for selected dates -->
                <input type="hidden" id="selected-dates-input" name="preferred_dates[]">

                <!-- Date & Time Selection -->
                <div class="form-section">
                    <h6 class="section-title">Date & Time</h6>
                    <div class="mb-3">
                        <label for="selected-datetime" class="form-label">Selected Date & Time</label>
                        <input type="datetime-local" id="selected-datetime" class="form-control" readonly>
                        <small class="form-text text-muted">Click on calendar to select</small>
                    </div>
                </div>

                <!-- Inspection Type -->
                <div class="form-section">
                    <h6 class="section-title">Inspection Details</h6>
                    <div class="mb-3">
                        <label for="inspection-type" class="form-label">Inspection Type <span class="text-danger">*</span></label>
                        <select id="inspection-type" name="inspection_type" class="form-control" required>
                            <option value="">-- Select Inspection Type --</option>
                            <option value="general">General Home Inspection</option>
                            <option value="pest">Pest Inspection</option>
                            <option value="mold">Mold Inspection</option>
                            <option value="radon">Radon Testing</option>
                            <option value="termite">Termite Inspection</option>
                            <option value="roof">Roof Inspection</option>
                            <option value="foundation">Foundation Inspection</option>
                            <option value="electrical">Electrical Inspection</option>
                            <option value="plumbing">Plumbing Inspection</option>
                        </select>
                    </div>
                </div>

                <!-- Contact Preferences -->
                <div class="form-section">
                    <h6 class="section-title">Contact Preferences</h6>
                    <div class="mb-3">
                        <label for="contact-method" class="form-label">Preferred Contact Method <span class="text-danger">*</span></label>
                        <select id="contact-method" name="contact_method" class="form-control" required>
                            <option value="">-- Select Contact Method --</option>
                            <option value="phone">Phone Call</option>
                            <option value="email">Email</option>
                            <option value="sms">SMS Text Message</option>
                            <option value="whatsapp">WhatsApp</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="contact-number" class="form-label">Phone Number</label>
                        <input type="tel" id="contact-number" name="contact_number" class="form-control" placeholder="+1 (555) 000-0000">
                    </div>

                    <div class="mb-3">
                        <label for="contact-email" class="form-label">Email Address</label>
                        <input type="email" id="contact-email" name="contact_email" class="form-control" placeholder="your@email.com">
                    </div>
                </div>

                <!-- Notes & Comments -->
                <div class="form-section">
                    <h6 class="section-title">Additional Information</h6>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes & Comments</label>
                        <textarea id="notes" name="notes" class="form-control" rows="4" placeholder="Any special instructions or notes for the inspector..."></textarea>
                        <small class="form-text text-muted">Max 500 characters</small>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" id="access-instructions" name="access_instructions" class="form-check-input">
                        <label class="form-check-label" for="access-instructions">
                            I will provide access instructions
                        </label>
                    </div>

                    <div id="access-instructions-field" class="mb-3" style="display: none;">
                        <label for="access-details" class="form-label">Access Instructions</label>
                        <textarea id="access-details" name="access_details" class="form-control" rows="3" placeholder="Gate code, key location, special entry instructions..."></textarea>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-calendar-check"></i> Submit Appointment Request
                    </button>
                    <button type="button" class="btn btn-secondary btn-lg w-100 mt-2" id="cancel-form">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Overlay for slide panel -->
    <div class="form-panel-overlay" id="form-panel-overlay"></div>

    @push('styles')
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
    <style>
        .inspection-scheduler {
            padding: 2rem;
            background-color: #f8f9fa;
            min-height: 100vh;
        }

        .scheduler-header {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            margin-bottom: 1.5rem;
        }

        .scheduler-header h1 {
            font-size: 2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            font-size: 1rem;
            color: #666;
            margin: 0;
        }

        .controls-section {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1.5rem;
            align-items: flex-end;
        }

        .control-label {
            display: block;
            font-weight: 600;
            font-size: 0.9rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .timezone-selector {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 0.5rem 0.75rem;
            font-size: 0.95rem;
        }

        .timezone-selector:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        .view-control {
            display: flex;
            flex-direction: column;
        }

        .btn-group {
            display: flex;
            gap: 0.5rem;
        }

        .view-toggle {
            flex: 1;
            padding: 0.5rem;
            font-size: 0.9rem;
            border: 1px solid #ddd;
            background: white;
            color: #0d6efd;
            transition: all 0.3s ease;
        }

        .view-toggle.active {
            background: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }

        .selected-dates-info {
            text-align: right;
        }

        .badge {
            padding: 0.5rem 1rem;
            font-size: 0.95rem;
        }

        .calendar-wrapper {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .full-calendar {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        /* FullCalendar Customization */
        .fc {
            font-size: 0.95rem;
        }

        .fc .fc-button-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .fc .fc-button-primary:hover {
            background-color: #0b5ed7;
            border-color: #0b5ed7;
        }

        .fc .fc-button-primary.fc-button-active {
            background-color: #0b5ed7;
            border-color: #0b5ed7;
        }

        .fc .fc-daygrid-day.fc-day-other {
            background-color: #f9f9f9;
        }

        .fc .fc-daygrid-day:hover {
            background-color: #f0f8ff;
            cursor: pointer;
        }

        .fc .fc-event {
            background-color: #28a745;
            border-color: #28a745;
            border-radius: 4px;
            padding: 2px 4px;
        }

        .fc .fc-event.booked {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .fc .fc-event.selected {
            background-color: #0d6efd;
            border-color: #0d6efd;
            box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.5);
        }

        .selected-dates-panel {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .selected-dates-panel h5 {
            font-weight: 600;
            margin-bottom: 1rem;
            color: #333;
        }

        .selected-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
        }

        .date-badge {
            background: #e7f3ff;
            border: 1px solid #0d6efd;
            border-radius: 6px;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .date-badge:hover {
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.3);
        }

        .date-badge-content {
            flex: 1;
        }

        .date-badge-datetime {
            font-weight: 600;
            color: #0d6efd;
            margin-bottom: 0.25rem;
        }

        .date-badge-type {
            font-size: 0.85rem;
            color: #666;
        }

        .date-badge-remove {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            font-size: 1.2rem;
            padding: 0;
            margin-left: 0.5rem;
        }

        /* Slide-out Panel Styles */
        .form-slide-panel {
            position: fixed;
            right: -500px;
            top: 0;
            width: 500px;
            height: 100vh;
            background: white;
            box-shadow: -2px 0 10px rgba(0, 0, 0, 0.15);
            z-index: 1050;
            transition: right 0.3s ease;
            overflow-y: auto;
        }

        .form-slide-panel.active {
            right: 0;
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            background: #f8f9fa;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .panel-header h4 {
            margin: 0;
            font-weight: 600;
            color: #333;
        }

        .btn-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }

        .btn-close:hover {
            color: #333;
        }

        .panel-body {
            padding: 2rem;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .form-actions {
            position: sticky;
            bottom: 0;
            background: white;
            padding: 1.5rem 0;
            border-top: 1px solid #eee;
            margin-top: 2rem;
        }

        .form-panel-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .form-panel-overlay.active {
            display: block;
            opacity: 1;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .controls-section {
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
            }

            .selected-dates-info {
                grid-column: 1 / -1;
                text-align: left;
            }
        }

        @media (max-width: 768px) {
            .inspection-scheduler {
                padding: 1rem;
            }

            .scheduler-header {
                padding: 1.5rem;
            }

            .controls-section {
                grid-template-columns: 1fr;
            }

            .form-slide-panel {
                width: 100%;
                right: -100%;
            }

            .selected-list {
                grid-template-columns: 1fr;
            }

            .calendar-wrapper {
                padding: 1rem;
            }
        }

        /* Print Styles */
        @media print {
            .controls-section,
            .form-slide-panel,
            .form-panel-overlay {
                display: none;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales-all.global.min.js'></script>
    <script>
        // Configuration and State Management
        const schedulerConfig = {
            inspectionId: '{{ $inspection->id }}',
            maxSelections: 3,
            selectedDates: [],
            currentTimezone: 'auto',
            currentView: 'dayGridMonth'
        };

        // Initialize Calendar
        document.addEventListener('DOMContentLoaded', function() {
            initializeCalendar();
            initializeEventListeners();
            loadExistingAppointments();
            detectUserTimezone();
        });

        function initializeCalendar() {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: ''
                },
                height: 'auto',
                contentHeight: 'auto',
                editable: false,
                selectable: true,
                selectConstraint: 'businessHours',
                businessHours: {
                    daysOfWeek: [1, 2, 3, 4, 5],
                    startTime: '08:00',
                    endTime: '18:00'
                },
                dateClick: function(info) {
                    handleDateClick(info, calendar);
                },
                eventClick: function(info) {
                    handleEventClick(info);
                },
                events: function(info, successCallback, failureCallback) {
                    loadCalendarEvents(successCallback, failureCallback);
                }
            });

            calendar.render();
            window.fullCalendar = calendar;
        }

        function initializeEventListeners() {
            // Timezone selector
            document.getElementById('timezone-select').addEventListener('change', function(e) {
                schedulerConfig.currentTimezone = e.target.value;
                updateCalendarTimezone();
            });

            // View toggle buttons
            document.querySelectorAll('.view-toggle').forEach(btn => {
                btn.addEventListener('click', function() {
                    const view = this.dataset.view;
                    document.querySelectorAll('.view-toggle').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    window.fullCalendar.changeView(view);
                    schedulerConfig.currentView = view;
                });
            });

            // Set initial active view button
            document.querySelector('[data-view="dayGridMonth"]').classList.add('active');

            // Slide panel controls
            document.getElementById('close-panel').addEventListener('click', closeFormPanel);
            document.getElementById('form-panel-overlay').addEventListener('click', closeFormPanel);
            document.getElementById('cancel-form').addEventListener('click', closeFormPanel);

            // Access instructions checkbox
            document.getElementById('access-instructions').addEventListener('change', function() {
                document.getElementById('access-instructions-field').style.display = this.checked ? 'block' : 'none';
            });

            // Form submission
            document.getElementById('inspection-form').addEventListener('submit', function(e) {
                e.preventDefault();
                submitAppointmentRequest();
            });
        }

        function handleDateClick(info, calendar) {
            if (schedulerConfig.selectedDates.length >= schedulerConfig.maxSelections) {
                alert(`You can only select up to ${schedulerConfig.maxSelections} dates.`);
                return;
            }

            // Check if date is already selected
            const dateStr = info.dateStr;
            if (schedulerConfig.selectedDates.some(d => d.date === dateStr)) {
                removeSelectedDate(dateStr);
                return;
            }

            // Open form panel with selected date
            document.getElementById('selected-datetime').value = info.dateStr + 'T09:00';
            openFormPanel(info.dateStr);
        }

        function handleEventClick(info) {
            // Show event details
            alert(`Appointment: ${info.event.title}\nTime: ${info.event.start}`);
        }

        function openFormPanel(selectedDate) {
            document.getElementById('form-slide-panel').classList.add('active');
            document.getElementById('form-panel-overlay').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeFormPanel() {
            document.getElementById('form-slide-panel').classList.remove('active');
            document.getElementById('form-panel-overlay').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        function addSelectedDate(dateTime, inspectionType = '', contactMethod = '') {
            if (schedulerConfig.selectedDates.length >= schedulerConfig.maxSelections) {
                return;
            }

            const dateObj = new Date(dateTime);
            const dateStr = dateObj.toISOString().split('T')[0];

            // Prevent duplicates
            if (schedulerConfig.selectedDates.some(d => d.date === dateStr)) {
                return;
            }

            schedulerConfig.selectedDates.push({
                date: dateStr,
                datetime: dateTime,
                inspectionType: inspectionType,
                contactMethod: contactMethod
            });

            updateSelectedDatesList();
            updateSelectedCount();
        }

        function removeSelectedDate(dateStr) {
            schedulerConfig.selectedDates = schedulerConfig.selectedDates.filter(d => d.date !== dateStr);
            updateSelectedDatesList();
            updateSelectedCount();
        }

        function updateSelectedDatesList() {
            const listEl = document.getElementById('selected-dates-list');

            if (schedulerConfig.selectedDates.length === 0) {
                listEl.innerHTML = '<p class="text-muted">No dates selected yet</p>';
                return;
            }

            listEl.innerHTML = schedulerConfig.selectedDates.map((item, index) => `
                <div class="date-badge">
                    <div class="date-badge-content">
                        <div class="date-badge-datetime">
                            ${new Date(item.datetime).toLocaleString('en-US', {
                                weekday: 'short',
                                month: 'short',
                                day: 'numeric',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            })}
                        </div>
                        ${item.inspectionType ? `<div class="date-badge-type">${item.inspectionType}</div>` : ''}
                    </div>
                    <button type="button" class="date-badge-remove" onclick="removeSelectedDate('${item.date}')">
                        ×
                    </button>
                </div>
            `).join('');
        }

        function updateSelectedCount() {
            document.getElementById('selected-count').textContent = schedulerConfig.selectedDates.length;
        }

        function detectUserTimezone() {
            const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            document.getElementById('timezone-select').value = timezone;
            schedulerConfig.currentTimezone = timezone;
        }

        function updateCalendarTimezone() {
            // Reload calendar with new timezone
            window.fullCalendar.refetchEvents();
        }

        function loadCalendarEvents(successCallback, failureCallback) {
            // Mock API call - replace with actual endpoint
            fetch(`/api/inspections/{{ $inspection->id }}/appointments`)
                .then(response => response.json())
                .then(data => {
                    const events = data.appointments.map(apt => ({
                        id: apt.id,
                        title: apt.title || 'Booked',
                        start: apt.start_time,
                        end: apt.end_time,
                        className: 'booked',
                        extendedProps: {
                            status: apt.status,
                            type: apt.type
                        }
                    }));
                    successCallback(events);
                })
                .catch(() => {
                    // Return empty array on error
                    successCallback([]);
                });
        }

        function loadExistingAppointments() {
            // This will be called automatically by FullCalendar's events callback
        }

        function submitAppointmentRequest() {
            const formData = new FormData(document.getElementById('inspection-form'));

            // Add selected dates
            schedulerConfig.selectedDates.forEach(item => {
                formData.append('preferred_dates[]', item.datetime);
            });

            fetch(document.getElementById('inspection-form').action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Appointment request submitted successfully!');
                    closeFormPanel();
                    schedulerConfig.selectedDates = [];
                    updateSelectedDatesList();
                    updateSelectedCount();
                    window.fullCalendar.refetchEvents();
                } else {
                    alert('Error: ' + (data.message || 'Failed to submit request'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting your request.');
            });
        }
    </script>
    @endpush
@endsection