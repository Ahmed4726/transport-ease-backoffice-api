@extends('admin.layouts.app')

@section('title', 'Create Trip')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Create Trip</h2>
            <p class="text-muted">Add a new driver trip.</p>
        </div>
        <a href="{{ route('admin.trips.index') }}" class="btn btn-secondary">Back to trips</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.trips.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Driver</label>
                        <select name="driver_id" class="form-select @error('driver_id') is-invalid @enderror" required>
                            <option value="">Select Driver</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->user->name }} ({{ $driver->user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('driver_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Approved Vehicle</label>
                        <input type="text" id="selected_vehicle_display" class="form-control" readonly value="">
                        <input type="hidden" name="vehicle_id" id="vehicle_id" value="{{ old('vehicle_id') }}">
                        <div class="form-text">The selected driver’s approved vehicle will appear here.</div>
                        @error('vehicle_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Start City</label>
                        <select id="start_city_select" class="form-select" required>
                            <option value="">Select start city</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">End City</label>
                        <select id="end_city_select" class="form-select" required>
                            <option value="">Select end city</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Start Stop</label>
                        <select name="from_stop_id" id="from_stop_id" class="form-select @error('from_stop_id') is-invalid @enderror" required>
                            <option value="">Choose a stop in the start city</option>
                        </select>
                        @error('from_stop_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">End Stop</label>
                        <select name="to_stop_id" id="to_stop_id" class="form-select @error('to_stop_id') is-invalid @enderror" required>
                            <option value="">Choose a stop in the end city</option>
                        </select>
                        @error('to_stop_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Intermediate Stops</label>
                        <div id="intermediate_stops_wrap" class="border rounded-3 p-3 bg-light">
                            <div class="row g-2" id="intermediate_stops"></div>
                            <div class="form-text mt-2">Select stops between the start and end city.</div>
                        </div>
                        <input type="hidden" name="selected_stop_ids" id="selected_stop_ids_hidden" value="">
                        @error('selected_stop_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-12">
                        <div id="route-preview" class="border rounded-3 p-3 bg-white" style="min-height: 360px;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0">Live Route Preview</h6>
                                <span class="badge bg-primary" id="route_summary">No stops selected</span>
                            </div>
                            <div id="trip_route_map" class="rounded-3 border" style="height: 240px;"></div>
                            <div class="mt-3">
                                <div class="fw-semibold mb-2">Selected stops</div>
                                <ol id="route_stop_list" class="mb-0 ps-3"></ol>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Total Seats</label>
                        <input type="number" id="total_seats" name="total_seats" value="{{ old('total_seats') }}" class="form-control @error('total_seats') is-invalid @enderror" min="1" required>
                        @error('total_seats')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Vehicle Capacity</label>
                        <input type="text" id="vehicle_capacity_display" class="form-control" readonly value="{{ old('driver_id') && !empty($driverVehicleMap[old('driver_id')]) ? 'Total: ' . $driverVehicleMap[old('driver_id')]['total_seats'] . ' • Available: ' . $driverVehicleMap[old('driver_id')]['available_seats'] : '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Available Seats</label>
                        <input type="number" id="available_seats" name="available_seats" value="{{ old('available_seats') }}" class="form-control @error('available_seats') is-invalid @enderror" min="1" required>
                        @error('available_seats')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <input type="hidden" name="is_instant" value="1">
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Create Trip</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const driverSelect = document.querySelector('select[name="driver_id"]');
    const vehicleIdInput = document.getElementById('vehicle_id');
    const vehicleDisplayInput = document.getElementById('selected_vehicle_display');
    const vehicleCapacityInput = document.getElementById('vehicle_capacity_display');
    const totalSeatsInput = document.getElementById('total_seats');
    const availableSeatsInput = document.getElementById('available_seats');
    const driverVehicleMap = @json($driverVehicleMap);
    const citiesData = @json($citiesData);
    const startCitySelect = document.getElementById('start_city_select');
    const endCitySelect = document.getElementById('end_city_select');
    const fromStopSelect = document.getElementById('from_stop_id');
    const toStopSelect = document.getElementById('to_stop_id');
    const intermediateWrap = document.getElementById('intermediate_stops');
    const routeSummary = document.getElementById('route_summary');
    const routeMapElement = document.getElementById('trip_route_map');
    const routeStopList = document.getElementById('route_stop_list');
    const selectedStopIdsInput = document.getElementById('selected_stop_ids_hidden');
    const selectedStops = [];
    let routeMapInstance = null;
    let routeMarkers = [];
    let routePolyline = null;

    function haversineDistanceKm(start, end) {
        if (!start || !end || start.latitude == null || start.longitude == null || end.latitude == null || end.longitude == null) {
            return 0;
        }

        const toRad = (value) => (value * Math.PI) / 180;
        const earthRadiusKm = 6371;
        const lat1 = toRad(parseFloat(start.latitude));
        const lon1 = toRad(parseFloat(start.longitude));
        const lat2 = toRad(parseFloat(end.latitude));
        const lon2 = toRad(parseFloat(end.longitude));
        const deltaLat = lat2 - lat1;
        const deltaLon = lon2 - lon1;
        const a = Math.sin(deltaLat / 2) ** 2 + Math.cos(lat1) * Math.cos(lat2) * Math.sin(deltaLon / 2) ** 2;

        return earthRadiusKm * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function estimateRouteMetrics(routeStops) {
        if (!routeStops || routeStops.length < 2) {
            return { distanceKm: 0, durationMinutes: 0 };
        }

        let distanceKm = 0;
        for (let i = 0; i < routeStops.length - 1; i++) {
            distanceKm += haversineDistanceKm(routeStops[i], routeStops[i + 1]);
        }

        return {
            distanceKm,
            durationMinutes: Math.max(20, Math.round((distanceKm / 35) * 60)),
        };
    }

    function calculateRouteMetrics(routeStops) {
        if (!routeStops || routeStops.length < 2 || typeof google === 'undefined' || !google.maps) {
            return estimateRouteMetrics(routeStops);
        }

        const validStops = routeStops.filter(stop => stop.latitude != null && stop.longitude != null);
        if (validStops.length < 2) {
            return estimateRouteMetrics(routeStops);
        }

        const origins = validStops.slice(0, -1).map(stop => new google.maps.LatLng(parseFloat(stop.latitude), parseFloat(stop.longitude)));
        const destinations = validStops.slice(1).map(stop => new google.maps.LatLng(parseFloat(stop.latitude), parseFloat(stop.longitude)));

        return new Promise((resolve) => {
            const service = new google.maps.DistanceMatrixService();
            service.getDistanceMatrix({
                origins,
                destinations,
                travelMode: google.maps.TravelMode.DRIVING,
                unitSystem: google.maps.UnitSystem.METRIC,
                avoidHighways: false,
                avoidTolls: false,
            }, (response, status) => {
                if (status !== 'OK' || !response || !response.rows) {
                    resolve(estimateRouteMetrics(routeStops));
                    return;
                }

                let totalDistanceMeters = 0;
                let totalDurationSeconds = 0;

                response.rows.forEach((row) => {
                    row.elements.forEach((element) => {
                        if (element && element.distance) {
                            totalDistanceMeters += Number(element.distance.value || 0);
                        }
                        if (element && element.duration) {
                            totalDurationSeconds += Number(element.duration.value || 0);
                        }
                    });
                });

                resolve({
                    distanceKm: totalDistanceMeters ? totalDistanceMeters / 1000 : 0,
                    durationMinutes: totalDurationSeconds ? Math.max(20, Math.round(totalDurationSeconds / 60)) : 0,
                });
            });
        });
    }

    function syncVehicleDetails() {
        const driverId = driverSelect?.value;
        const vehicleData = driverVehicleMap[driverId] || null;

        if (vehicleData) {
            vehicleIdInput.value = vehicleData.id;
            vehicleDisplayInput.value = vehicleData.label;
            vehicleCapacityInput.value = vehicleData
                ? `Total: ${vehicleData.total_seats ?? 0} • Available: ${vehicleData.available_seats ?? 0}`
                : 'No approved vehicle';
            const maxSeats = vehicleData.available_seats;
            totalSeatsInput.max = maxSeats;
            availableSeatsInput.max = maxSeats;
            totalSeatsInput.min = 1;
            availableSeatsInput.min = 1;

            if (Number(totalSeatsInput.value) > maxSeats) {
                totalSeatsInput.value = maxSeats;
            }

            if (Number(availableSeatsInput.value) > maxSeats) {
                availableSeatsInput.value = maxSeats;
            }

            if (Number(availableSeatsInput.value) > Number(totalSeatsInput.value)) {
                availableSeatsInput.value = totalSeatsInput.value || 1;
            }
        } else {
            vehicleIdInput.value = '';
            vehicleDisplayInput.value = '';
            vehicleCapacityInput.value = '';
            totalSeatsInput.removeAttribute('max');
            availableSeatsInput.removeAttribute('max');
            totalSeatsInput.min = 1;
            availableSeatsInput.min = 1;
        }
    }

    function getCityById(cityId) {
        return citiesData.find(city => String(city.id) === String(cityId)) || null;
    }

    function renderStopsForCity(selectElement, cityId, selectedValue) {
        const city = getCityById(cityId);
        const options = city ? city.stops : [];
        selectElement.innerHTML = '<option value="">Choose a stop</option>';
        options.forEach(stop => {
            const option = document.createElement('option');
            option.value = stop.id;
            option.textContent = `${stop.city_name} - ${stop.label}`;
            if (String(selectedValue) === String(stop.id)) {
                option.selected = true;
            }
            selectElement.appendChild(option);
        });
    }

    function getStopById(stopId) {
        if (!stopId) {
            return null;
        }

        for (const city of citiesData) {
            const stop = city.stops.find(item => String(item.id) === String(stopId));
            if (stop) {
                return { ...stop, city_name: city.name };
            }
        }

        return null;
    }

    function getStopProgress(stop, startPoint, endPoint) {
        if (!stop || stop.latitude == null || stop.longitude == null || !startPoint || !endPoint) {
            return null;
        }

        const startLng = parseFloat(startPoint.longitude);
        const startLat = parseFloat(startPoint.latitude);
        const endLng = parseFloat(endPoint.longitude);
        const endLat = parseFloat(endPoint.latitude);
        const stopLng = parseFloat(stop.longitude);
        const stopLat = parseFloat(stop.latitude);

        if (!Number.isFinite(startLng) || !Number.isFinite(startLat) || !Number.isFinite(endLng) || !Number.isFinite(endLat) || !Number.isFinite(stopLng) || !Number.isFinite(stopLat)) {
            return null;
        }

        const segmentDx = endLng - startLng;
        const segmentDy = endLat - startLat;
        const segmentLengthSquared = (segmentDx * segmentDx) + (segmentDy * segmentDy);

        if (segmentLengthSquared <= 0) {
            return null;
        }

        return ((stopLng - startLng) * segmentDx + (stopLat - startLat) * segmentDy) / segmentLengthSquared;
    }

    function compareStopsByProgress(a, b, startPoint, endPoint) {
        const progressA = getStopProgress(a, startPoint, endPoint);
        const progressB = getStopProgress(b, startPoint, endPoint);

        if (progressA == null && progressB == null) {
            return 0;
        }
        if (progressA == null) {
            return 1;
        }
        if (progressB == null) {
            return -1;
        }

        return progressA - progressB;
    }

    function renderIntermediateStops() {
        const selectedFromStop = getStopById(fromStopSelect.value);
        const selectedToStop = getStopById(toStopSelect.value);
        intermediateWrap.innerHTML = '';

        if (!selectedFromStop || !selectedToStop) {
            intermediateWrap.innerHTML = '<div class="text-muted">Choose both start and end stops to see intermediate stops.</div>';
            return;
        }

        if (selectedFromStop.latitude == null || selectedFromStop.longitude == null || selectedToStop.latitude == null || selectedToStop.longitude == null) {
            intermediateWrap.innerHTML = '<div class="text-muted">The selected start and end stops must have coordinates.</div>';
            return;
        }

        const candidateStops = [];
        const seen = new Set();

        for (const city of citiesData) {
            const cityStops = city.stops || [];
            for (const stop of cityStops) {
                if (!stop || stop.latitude == null || stop.longitude == null) {
                    continue;
                }

                if (String(stop.id) === String(selectedFromStop.id) || String(stop.id) === String(selectedToStop.id)) {
                    continue;
                }

                const progress = getStopProgress(stop, selectedFromStop, selectedToStop);
                if (progress != null && progress > 0 && progress < 1) {
                    const key = `${stop.id}`;
                    if (!seen.has(key)) {
                        seen.add(key);
                        candidateStops.push({ ...stop, city_name: city.name });
                    }
                }
            }
        }

        candidateStops.sort((a, b) => compareStopsByProgress(a, b, selectedFromStop, selectedToStop));

        const selectedIds = new Set(selectedStops.map(stop => String(stop.id)));
        const chips = document.createElement('div');
        chips.className = 'row g-2';

        candidateStops.forEach(stop => {
            const col = document.createElement('div');
            col.className = 'col-md-4';
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `btn btn-sm w-100 ${selectedIds.has(String(stop.id)) ? 'btn-primary' : 'btn-outline-secondary'}`;
            btn.textContent = `${stop.city_name} - ${stop.label}`;
            btn.addEventListener('click', () => {
                const existingIndex = selectedStops.findIndex(item => String(item.id) === String(stop.id));
                if (existingIndex >= 0) {
                    selectedStops.splice(existingIndex, 1);
                } else {
                    selectedStops.push(stop);
                }
                renderIntermediateStops();
            });
            col.appendChild(btn);
            chips.appendChild(col);
        });

        if (!candidateStops.length) {
            intermediateWrap.innerHTML = '<div class="text-muted">No intermediate stops were found for the selected path.</div>';
            return;
        }

        intermediateWrap.appendChild(chips);
        syncSelectedStops();
    }

    function renderRouteMap(routeStops) {
        if (!routeMapElement) {
            return;
        }

        if (typeof google === 'undefined' || !google.maps) {
            routeMapElement.innerHTML = '<div class="text-muted">Loading Google Maps preview...</div>';
            const reRender = () => renderRouteMap(routeStops);
            if (!window.__tripMapReadyBound) {
                window.__tripMapReadyBound = true;
                document.addEventListener('google-maps-ready', reRender, { once: true });
            }
            return;
        }

        if (!routeMapInstance) {
            routeMapElement.innerHTML = '';
            routeMapInstance = new google.maps.Map(routeMapElement, {
                center: { lat: 24.8607, lng: 67.0011 },
                zoom: 5,
                disableDefaultUI: true,
            });
        }

        routeMarkers.forEach(marker => marker.setMap(null));
        routeMarkers = [];
        if (routePolyline) {
            routePolyline.setMap(null);
            routePolyline = null;
        }

        const validStops = routeStops.filter((stop) => {
            const latitude = Number.parseFloat(stop.latitude);
            const longitude = Number.parseFloat(stop.longitude);
            return Number.isFinite(latitude) && Number.isFinite(longitude) && latitude >= -90 && latitude <= 90 && longitude >= -180 && longitude <= 180;
        });

        if (!validStops.length) {
            if (routeMapInstance) {
                routeMapInstance.setCenter({ lat: 24.8607, lng: 67.0011 });
                routeMapInstance.setZoom(5);
                google.maps.event.trigger(routeMapInstance, 'resize');
            }
            return;
        }

        const bounds = new google.maps.LatLngBounds();
        validStops.forEach((stop) => {
            const latitude = Number.parseFloat(stop.latitude);
            const longitude = Number.parseFloat(stop.longitude);
            const position = { lat: latitude, lng: longitude };
            bounds.extend(position);
            const marker = new google.maps.Marker({
                position,
                map: routeMapInstance,
                title: stop.label,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 7,
                    fillColor: stop.type === 'start' ? '#16a34a' : stop.type === 'end' ? '#dc2626' : '#2563eb',
                    fillOpacity: 1,
                    strokeWeight: 1,
                    strokeColor: '#fff',
                },
            });
            routeMarkers.push(marker);
        });

        if (validStops.length > 1) {
            routePolyline = new google.maps.Polyline({
                path: validStops.map(stop => ({ lat: parseFloat(stop.latitude), lng: parseFloat(stop.longitude) })),
                map: routeMapInstance,
                strokeColor: '#2563eb',
                strokeWeight: 3,
            });
        }

        google.maps.event.trigger(routeMapInstance, 'resize');
        routeMapInstance.fitBounds(bounds);
    }

    async function syncSelectedStops() {
        const selectedIds = selectedStops.map(stop => stop.id);
        selectedStopIdsInput.value = selectedIds.join(',');
        const routeStops = [];
        const startStop = getStopById(fromStopSelect.value);
        const endStop = getStopById(toStopSelect.value);

        if (startStop) {
            routeStops.push({ ...startStop, type: 'start' });
        }
        selectedStops.forEach(stop => routeStops.push({ ...stop, type: 'intermediate' }));
        if (endStop) {
            routeStops.push({ ...endStop, type: 'end' });
        }

        routeStops.sort((a, b) => {
            const progressA = getStopProgress(a, startStop ? { latitude: startStop.latitude, longitude: startStop.longitude } : null, endStop ? { latitude: endStop.latitude, longitude: endStop.longitude } : null);
            const progressB = getStopProgress(b, startStop ? { latitude: startStop.latitude, longitude: startStop.longitude } : null, endStop ? { latitude: endStop.latitude, longitude: endStop.longitude } : null);
            if (progressA == null && progressB == null) {
                return 0;
            }
            if (progressA == null) {
                return 1;
            }
            if (progressB == null) {
                return -1;
            }
            return progressA - progressB;
        });

        const stopItems = routeStops.map((item, index) => `<li class="mb-2"><span class="badge rounded-pill ${item.type === 'start' ? 'bg-success' : item.type === 'end' ? 'bg-danger' : 'bg-info'} me-2">${index + 1}</span>${item.city_name ? `${item.city_name} - ${item.label}` : item.label}</li>`).join('');
        routeStopList.innerHTML = routeStops.length ? stopItems : '<li class="text-muted">Pick a start city and end city to see the route build live.</li>';

        const routeMetrics = await calculateRouteMetrics(routeStops);
        const distanceKm = Number(routeMetrics.distanceKm || 0);
        const etaMinutes = Number(routeMetrics.durationMinutes || 0);

        routeSummary.textContent = routeStops.length ? `${routeStops.length} stops • ${distanceKm.toFixed(1)} km • ${etaMinutes} min` : 'No stops selected';
        renderRouteMap(routeStops);
    }

    startCitySelect?.addEventListener('change', () => {
        renderStopsForCity(fromStopSelect, startCitySelect.value, '');
        renderIntermediateStops();
    });

    endCitySelect?.addEventListener('change', () => {
        renderStopsForCity(toStopSelect, endCitySelect.value, '');
        renderIntermediateStops();
    });

    fromStopSelect?.addEventListener('change', () => {
        renderIntermediateStops();
    });
    toStopSelect?.addEventListener('change', () => {
        renderIntermediateStops();
    });

    driverSelect?.addEventListener('change', syncVehicleDetails);
    syncVehicleDetails();
    renderIntermediateStops();
});
</script>
@endsection
