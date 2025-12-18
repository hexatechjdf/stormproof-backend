@extends('layouts.homeowner')
@section('title', 'Schedule Inspection')

@section('content')
    <h1>Schedule Your Inspection</h1>
    <p>Please select up to 3 preferred dates and times for your inspection.</p>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">

            <form action="{{ route('homeowner.inspections.schedule.store', $inspection->id ?? -1) }}" method="POST">
                @csrf

                <input type="hidden" name="preferred_dates[]" id="preferred_date_1" required>
                <input type="hidden" name="preferred_dates[]" id="preferred_date_2">
                <input type="hidden" name="preferred_dates[]" id="preferred_date_3">
                @if (!$inspection?->id)
                    <div class="mb-3">
                        <label for="inspection_title" class="form-label fw-semibold">Inspection Title</label>
                        <input type="text" name="inspection_title" id="inspection_title"
                            class="form-control @error('inspection_title') is-invalid @enderror"
                            value="{{ old('inspection_title', $inspection->title ?? '') }}" required
                            placeholder="Enter inspection title" @if ($inspection?->id) readonly @endif>
                        @error('inspection_title')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                @endif
                <h4>Your Selected Time Slots</h4>
                <ul id="selectedSlotsList" class="mb-3"></ul>

                <style>
                    :root {
                        --calendar-time-color: #00a2ff;
                        --calendar-border-color: #d6d6d6;
                    }

                    .calendar-picker-data {
                        flex: 1 1 auto;
                        justify-content: space-around;
                    }

                    .month-data.d-flex {
                        display: flex;
                        padding: 5px;
                        flex: 1 1 0;
                        min-width: 300px;
                        max-width: 50%;
                    }

                    .date-day.disabled {
                        cursor: not-allowed;
                        color: #ccc;
                    }

                    .date-day.enabled {
                        color: var(--calendar-time-color);
                    }

                    .date-day {
                        border: 1px solid var(--calendar-border-color);
                        cursor: pointer;
                        text-align: center;
                    }

                    .dates-available {
                        display: grid;
                        grid-template-columns: repeat(1, 1fr);
                        grid-gap: 10px;
                        padding: 10px
                    }

                    .times-available {
                        width: 50%;
                        max-width: 50%;
                        padding: 10px;
                        display: grid;
                        grid-template-columns: repeat(4, 1fr);
                        grid-gap: 10px;
                        max-height: 300px;
                        overflow-y: auto;
                    }

                    .times-available.no-time {
                        grid-template-columns: repeat(1, 1fr);
                    }

                    .time-pick {
                        padding: 12px;
                        cursor: pointer;
                        text-align: center;
                        border: 1px solid var(--calendar-border-color);
                        color: var(--calendar-time-color);
                        background: #fff;
                        transition: background 0.2s, color 0.2s;
                    }

                    .date-day.selected-date,
                    .date-day:hover:not(.no-date) {
                        background: var(--calendar-time-color);
                        color: #fff;
                        border: 0
                    }

                    .time-pick.selected_time {
                        background: var(--calendar-time-color);
                        color: #fff;
                    }

                    .time-pick:not(.selected_time):hover {
                        background: rgba(0, 162, 255, 0.2);
                        color: #000;
                    }
                </style>

                <div class="d-flex calendar-picker flex-column">
                    <div class="d-flex mb-3">
                        <select class="form-control" id="select-month" onchange="showTimeSlots('month')"></select>
                        <select class="form-control ms-2" id="select-year" onchange="showTimeSlots('year')"></select>
                        <select class="form-control ms-2" id="select-timezone"
                            onchange="showTimeSlots('timezone')"></select>
                    </div>

                    <label>Date & Time:
                        <span class="selected-date fw-bold"></span>
                        <span class="selected_time fw-bold"></span>
                    </label>

                    <div class="d-flex calendar-picker-data" style="background:#fff">
                        <div class="no-date-data w-100 p-5 text-center">No Dates available</div>
                        <div class="month-data calendar-data">
                            <div class="dates-available"></div>
                        </div>
                        <div class="times-available calendar-data"></div>

                        <input type="text" class="d-none" id="selected_time">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Submit for Approval</button>
                <a href="{{ route('homeowner.dashboard') }}" class="btn btn-secondary mt-3">Cancel</a>
            </form>
        </div>
    </div>


    <script>
        function createSlot(value, text) {
            var slotElement = document.createElement("span");
            slotElement.setAttribute('data-time', value);
            slotElement.classList.add('time-pick');
            slotElement.innerText = text;
            return slotElement;
        }

        let selectedtime = document.querySelector('#selected_time');

        function createDay(value, text, weekday) {
            var slotElement = document.createElement("span");
            slotElement.setAttribute('data-date', value);
            slotElement.classList.add('date-day', `date-day-${text}`);
            slotElement.onclick = function(e) {
                var selectedDate = document.querySelector('.selected-date');
                selectedtime.value = '';
                var selectedTime = document.querySelector('.selected_time');

                selectedTime.textContent = '';
                let dt = this.getAttribute('data-date');
                userdate = dt;
                let day = new Date(dt);
                selectedDate.textContent = day.toLocaleDateString();
                $('.date-day').removeClass('selected-date');
                this.classList.add('selected-date');
                handleSlots(this.getAttribute('data-date'));
            }
            slotElement.innerHTML = `<div class="day-data d-flex flex-column">
            <span class="day-week">${weekdays[weekday]}</span>
            <span class="day-date">${text}</span>
            </div>`;
            return slotElement;
        }

        function getTimeFromDate(dateString) {

            var date = new Date(dateString);
            var time = date.toLocaleTimeString([], {
                hour: 'numeric',
                minute: 'numeric',
                hour12: true
            });
            return time;
        }

        function addZero(x) {
            return (x.toString().length == 1 ? '0' : "") + x;
        }

        function createMonthCalendar(year, month, alloweddays = [], startday = 0) {
            var startDate = new Date(year, month - 1, 1);
            console.log(year, month, alloweddays);
            var startDay = (startDate.getDay() + startday) % 7;
            var numDays = new Date(year, month, 0).getDate();
            var calendarDays = [];
            for (var i = 0; i < startDay; i++) {

                calendarDays.push("");
            }
            for (var day = 1; day <= numDays; day++) {
                calendarDays.push(day);
            }
            var calendarHTML = `<table class="table">`;

            if (startday == 6) {
                calendarHTML +=
                    "<tr><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th><th>Sun</th></tr>";
            } else if (startday == 0) {
                calendarHTML +=
                    "<tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr>";
            }


            let istrigger = false;
            let lastdate = '';
            for (var j = 0; j < calendarDays.length; j++) {
                if (j % 7 === 0) {
                    calendarHTML += "<tr>";
                }
                let dayvalue = calendarDays[j] || "";
                if (dayvalue === "") {
                    calendarHTML += `<td class="date-day no-date disabled"></td>`;
                } else {
                    let currentdate = `${year}-${addZero(month)}-${addZero(dayvalue)}`;
                    let disabled = 'enabled';
                    if (!alloweddays.includes(currentdate)) {
                        disabled = 'disabled';
                    } else if (!istrigger || userdate == currentdate) {
                        istrigger = true;
                        lastdate = dayvalue;

                    }
                    calendarHTML +=
                        `<td class="date-day ${disabled} date-day-${dayvalue}" data-date=${currentdate}>${dayvalue}</td>`;
                }
                if (j % 7 === 6) {
                    calendarHTML += "</tr>";
                }
            }
            calendarHTML += "</table>";
            var calendarContainer = document.querySelector(".dates-available");
            calendarContainer.innerHTML = calendarHTML;
            $('body').off('click', '.date-day.enabled');
            $('body').on('click', '.date-day.enabled', function() {
                var selectedDate = document.querySelector('.selected-date');
                selectedtime.value = '';
                var selectedTime = document.querySelector('.selected_time');

                selectedTime.textContent = '';
                let dt = $(this).attr('data-date');
                userdate = dt;
                let day = new Date(dt);
                selectedDate.textContent = day.toLocaleDateString();
                $('.date-day').removeClass('selected-date');
                $(this).addClass('selected-date');
                handleSlots($(this).attr('data-date'));
            });
            if (lastdate != '') {
                setTimeout((dayvalue1) => {
                    document.querySelector('.date-day-' + dayvalue1).click();
                    document.querySelector('.date-day-' + dayvalue1).click();
                }, 300, lastdate);
            }
        }

        function getLastDayOfMonth(year, month) {
            const nextMonthDate = new Date(year, month, 1);
            const lastDayOfMonth = new Date(nextMonthDate - 1);
            return lastDayOfMonth.getDate();
        }

        function showTimeSlots(date = '') {
            if (date == '') {
                date = document.getElementById("selected-date").value
            }
            let ignore = false;
            if (['month', 'year', 'timezone'].includes(date)) {
                selectedyear = document.querySelector('#select-year').value;
                selectedmonth = document.querySelector('#select-month').value;
                let newusertime = document.querySelector('#select-timezone').value;

                if (newusertime != userTimezone) {
                    userTimezone = newusertime;
                    ignore = true;
                }
                selectedmonth = parseInt(selectedmonth) + 1;
                date = `${selectedyear}-${addZero(selectedmonth)}-01`;
            }

            var slots = availableSlots[date] || "";
            if (slots == '' || ignore) {

                if (noslots.includes(date) && !ignore) {
                    handleSlots(date);
                    return;
                }
                let day = new Date(date);
                let current_month = `${day.getFullYear()}-${addZero(day.getMonth() + 1)}-`;
                let start_date = new Date(`${current_month}01T00:01`);
                let end_date = new Date(
                    `${current_month}${addZero(getLastDayOfMonth(day.getFullYear(),day.getMonth() + 1))}T23:59`);

                fetch(`https://services.leadconnectorhq.com/appengine/appointment/free-slots?calendar_id=${calendarId}&startDate=${start_date.getTime()}&endDate=${end_date.getTime()}&timezone=${userTimezone}`, {
                    "headers": {
                        "accept": "*/*",
                        "accept-language": "en-US,en;q=0.9",
                    },
                    "method": "GET",
                }).then(t => {
                    return t.json();
                }).then(x => {
                    availableSlots = x;
                    var slotsContainer = document.querySelector(".dates-available");
                    slotsContainer.innerHTML = "";
                    let days = Object.keys(x);
                    let istrigger = false;
                    if (days.length <= 1) {
                        $('.no-date-data ').show();
                        $('.calendar-data').hide();
                        slotsContainer.innerHTML = 'No Dates available';
                        handleSlots('');
                    } else {
                        $('.no-date-data ').hide();
                        $('.calendar-data').show();

                        createMonthCalendar(day.getFullYear(), day.getMonth() + 1, days)

                    }

                });
            } else {
                handleSlots(date);
            }
        }

        function handleSlots(slots) {
            let date = '';
            if (typeof slots == 'string') {
                date = slots;
                slots = availableSlots[slots] || "";
            }
            document.querySelector('#selected_time').value = '';
            var slotsContainer = document.querySelector(".times-available");
            slotsContainer.innerHTML = "";
            slotsContainer.classList.remove('no-time');
            if (slots?.slots) {

                slots = slots.slots;
                for (var i = 0; i < slots.length; i++) {
                    var slot = slots[i];
                    slotsContainer.appendChild(createSlot(slot, getTimeFromDate(slot)));
                }
            } else {
                if (!noslots.includes(date)) {
                    noslots.push(date);
                }

                slotsContainer.classList.add('no-time');
                slotsContainer.appendChild(createSlot("", "No slots available for selected date."));
            }

        }

        var noslots = [];
        let weekdays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
        let monthnames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October",
            "November", "December"
        ];

        let timezones = [
            "Africa/Algiers",
            "Africa/Cairo",
            "Africa/Casablanca",
            "Africa/Harare",
            "Africa/Lagos",
            "Africa/Nairobi",
            "America/Argentina/Buenos_Aires",
            "America/Bahia_Banderas",
            "America/Belize",
            "America/Bogota",
            "America/Boise",
            "America/Caracas",
            "America/Chicago",
            "America/Chihuahua",
            "America/Dawson",
            "America/Denver",
            "America/Detroit",
            "America/Edmonton",
            "America/Glace_Bay",
            "America/Godthab",
            "America/Guatemala",
            "America/Indiana/Indianapolis",
            "America/Juneau",
            "America/Los_Angeles",
            "America/Louisville",
            "America/Managua",
            "America/Manaus",
            "America/Mexico_City",
            "America/Montevideo",
            "America/New_York",
            "America/Noronha",
            "America/Phoenix",
            "America/Regina",
            "America/Santiago",
            "America/Santo_Domingo",
            "America/Sao_Paulo",
            "America/St_Johns",
            "America/Tijuana",
            "America/Toronto",
            "Asia/Almaty",
            "Asia/Amman",
            "Asia/Baghdad",
            "Asia/Baku",
            "Asia/Bangkok",
            "Asia/Colombo",
            "Asia/Dhaka",
            "Asia/Dubai",
            "Asia/Irkutsk",
            "Asia/Jerusalem",
            "Asia/Kabul",
            "Asia/Karachi",
            "Asia/Kathmandu",
            "Asia/Kolkata",
            "Asia/Krasnoyarsk",
            "Asia/Kuala_Lumpur",
            "Asia/Kuwait",
            "Asia/Magadan",
            "Asia/Qatar",
            "Asia/Rangoon",
            "Asia/Seoul",
            "Asia/Shanghai",
            "Asia/Taipei",
            "Asia/Tehran",
            "Asia/Tokyo",
            "Asia/Vladivostok",
            "Asia/Yakutsk",
            "Asia/Yekaterinburg",
            "Atlantic/Azores",
            "Atlantic/Canary",
            "Atlantic/Cape_Verde",
            "Australia/Adelaide",
            "Australia/Brisbane",
            "Australia/Canberra",
            "Australia/Darwin",
            "Australia/Hobart",
            "Australia/Perth",
            "Australia/Sydney",
            "Canada/Atlantic",
            "Canada/Newfoundland",
            "Canada/Saskatchewan",
            "Etc/GMT+12",
            "Etc/GMT+2",
            "Etc/Greenwich",
            "Europe/Amsterdam",
            "Europe/Athens",
            "Europe/Belgrade",
            "Europe/Brussels",
            "Europe/Bucharest",
            "Europe/Helsinki",
            "Europe/London",
            "Europe/Madrid",
            "Europe/Moscow",
            "Europe/Oslo",
            "Europe/Sarajevo",
            "GMT",
            "Pacific/Auckland",
            "Pacific/Fiji",
            "Pacific/Guam",
            "Pacific/Honolulu",
            "Pacific/Midway",
            "Pacific/Tongatapu",
            "US/Alaska",
            "US/Arizona",
            "US/Central",
            "US/East-Indiana",
            "US/Eastern",
            "US/Mountain",
            "UTC"
        ];
        let calendarId = "{{ $primary_calendar }}";
        let availableSlots = {};
        let today = new Date();
        let selectedyear = today.getFullYear();
        let selectedmonth = today.getMonth() + 1;
        let userdate = '';
        let userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        showTimeSlots(today);

        function showMonths() {
            let months = document.querySelector('#select-month');
            months.innerHTML = "";
            monthnames.forEach((x, index) => {
                var month = document.createElement("option");
                month.value = index;
                month.text = x;
                if (index == selectedmonth - 1) {
                    month.selected = true;
                }

                months.appendChild(month);
            });
        }
        showMonths();
        let zonselector = document.querySelector('#select-timezone');
        timezones.forEach((x, index) => {
            var month = document.createElement("option");
            month.value = x;
            month.text = x.replaceAll('_', ' ');
            if (x == userTimezone) {
                month.selected = true;
            }

            zonselector.appendChild(month);
        });


        const currentYear = today.getFullYear();
        const containerElement = document.getElementById('select-year');
        for (let i = 0; i < 10; i++) {
            const year = currentYear + i;
            const optionElement = document.createElement('option');
            optionElement.value = year;
            optionElement.textContent = year;
            containerElement.appendChild(optionElement);
        }
    </script>

    <script>
        const selectedSlots = [];
        const list = document.getElementById("selectedSlotsList");

        function highlightSelectedSlots() {
            document.querySelectorAll(".time-pick").forEach(slotEl => {
                const time = slotEl.dataset.time;
                if (selectedSlots.includes(time)) {
                    slotEl.classList.add("selected_time");
                } else {
                    slotEl.classList.remove("selected_time");
                }
            });
        }

        document.body.addEventListener("click", function(e) {
            if (!e.target.classList.contains("time-pick")) return;
            const datetime = e.target.dataset.time;
            if (!datetime) return;

            const index = selectedSlots.indexOf(datetime);
            if (index > -1) {
                selectedSlots.splice(index, 1);
            } else {
                if (selectedSlots.length >= 3) {
                    alert("You can only select up to 3 preferred times.");
                    return;
                }
                selectedSlots.push(datetime);
            }

            renderSelectedSlots();
            highlightSelectedSlots();
        });


        function renderSelectedSlots() {
            list.innerHTML = "";

            selectedSlots.forEach((slot, i) => {
                list.innerHTML += `
                <li class="mb-3">${slot} 
                    <button type="button" onclick="removeSlot(${i})" class="btn btn-sm btn-danger">Remove</button>
                </li>
            `;
            });
            document.getElementById("preferred_date_1").value = selectedSlots[0] || "";
            document.getElementById("preferred_date_2").value = selectedSlots[1] || "";
            document.getElementById("preferred_date_3").value = selectedSlots[2] || "";

            highlightSelectedSlots();
        }

        function removeSlot(index) {
            selectedSlots.splice(index, 1);
            renderSelectedSlots();
        }

        function handleSlots(slots) {
            let date = '';
            if (typeof slots == 'string') {
                date = slots;
                slots = availableSlots[slots] || "";
            }

            document.querySelector('#selected_time').value = '';
            var slotsContainer = document.querySelector(".times-available");
            slotsContainer.innerHTML = "";
            slotsContainer.classList.remove('no-time');

            if (slots?.slots) {
                slots = slots.slots;
                for (var i = 0; i < slots.length; i++) {
                    var slot = slots[i];
                    const slotEl = createSlot(slot, getTimeFromDate(slot));
                    slotsContainer.appendChild(slotEl);
                }
            } else {
                if (!noslots.includes(date)) {
                    noslots.push(date);
                }
                slotsContainer.classList.add('no-time');
                slotsContainer.appendChild(createSlot("", "No slots available for selected date."));
            }
            highlightSelectedSlots();
        }
    </script>


@endsection
