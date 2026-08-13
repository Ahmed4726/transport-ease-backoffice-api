@extends('admin.layouts.app')

@php
    $latestLocation = $trip->locations()->latest('recorded_at')->first();
    $driverLocation = $latestLocation ? [
        'lat' => (float) $latestLocation->latitude,
        'lng' => (float) $latestLocation->longitude,
        'recorded_at' => $latestLocation->recorded_at?->toDateTimeString(),
    ] : null;

    $orderedTripStops = $trip->stops->sortBy('stop_order')->values();
    $tripStops = $orderedTripStops->map(function ($tripStop) {
        return [
            'lat' => $tripStop->stop?->latitude,
            'lng' => $tripStop->stop?->longitude,
            'name' => $tripStop->stop?->location_name ?: $tripStop->stop?->address ?: 'Stop ' . $tripStop->stop_order,
            'address' => $tripStop->stop?->address,
        ];
    })->filter(function ($stop) {
        return !empty($stop['lat']) && !empty($stop['lng']);
    })->values();

    $tripDistanceKm = 0;
    if ($tripStops->count() >= 2) {
        $earthRadiusKm = 6371;
        $toRad = fn ($value) => ($value * M_PI) / 180;

        for ($i = 0; $i < $tripStops->count() - 1; $i++) {
            $start = $tripStops[$i];
            $end = $tripStops[$i + 1];
            $lat1 = $toRad((float) $start['lat']);
            $lon1 = $toRad((float) $start['lng']);
            $lat2 = $toRad((float) $end['lat']);
            $lon2 = $toRad((float) $end['lng']);
            $deltaLat = $lat2 - $lat1;
            $deltaLon = $lon2 - $lon1;
            $a = sin($deltaLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($deltaLon / 2) ** 2;
            $tripDistanceKm += $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
        }
    }

    $tripEtaMinutes = $tripDistanceKm > 0 ? max(20, (int) round(($tripDistanceKm / 35) * 60)) : max(20, ($trip->stops->count() > 0 ? ($trip->stops->count() - 1) * 20 : 0));
@endphp

@section('title', 'Trip Detail')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Trip Detail</h2>
            <p class="text-muted">View trip route, stops, and driver assignment.</p>
        </div>
        <a href="{{ route('admin.trips.index') }}" class="btn btn-secondary">Back to trips</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="card-title">Trip summary</h5>
                    <dl class="row">
                        <dt class="col-5">Driver</dt>
                        <dd class="col-7">{{ $trip->driver->user->name ?? 'N/A' }}</dd>
                        <dt class="col-5">Vehicle</dt>
                        <dd class="col-7">{{ $trip->vehicle->brand ?? '' }} {{ $trip->vehicle->model ?? '' }}</dd>
                        <dt class="col-5">Status</dt>
                        <dd class="col-7"><span class="badge bg-{{ $trip->status === 'scheduled' ? 'secondary' : ($trip->status === 'started' ? 'info' : ($trip->status === 'completed' ? 'success' : 'danger')) }}">{{ ucfirst($trip->status) }}</span></dd>
                        @if($trip->status === 'started')
                            <dt class="col-5">Live</dt>
                            <dd class="col-7"><button type="button" class="btn btn-sm btn-success">Trip is live</button></dd>
                        @endif
                        <dt class="col-5">Seats</dt>
                        <dd class="col-7">{{ $trip->available_seats }} / {{ $trip->total_seats }}</dd>
                        <dt class="col-5">Stops</dt>
                        <dd class="col-7">{{ $trip->stops->count() }}</dd>
                        <dt class="col-5">Distance</dt>
                        <dd class="col-7">{{ number_format($tripDistanceKm ?? 0, 1) }} km</dd>
                        <dt class="col-5">ETA</dt>
                        <dd class="col-7" id="tripEtaLabel">{{ $tripEtaMinutes ?? 0 }} min</dd>
                        @if($driverLocation)
                            <dt class="col-5">Driver location</dt>
                            <dd class="col-7" id="driverLocationLabel">{{ number_format($driverLocation['lat'], 5) }}, {{ number_format($driverLocation['lng'], 5) }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title">Stops</h5>
                    @if($trip->stops->isNotEmpty())
                        <ol class="list-group list-group-numbered">
                            @foreach($trip->stops as $tripStop)
                                <li class="list-group-item">
                                    <strong>{{ $tripStop->stop?->location_name ?: $tripStop->stop?->address ?: 'Stop ' . $tripStop->stop_order }}</strong><br>
                                    <small>{{ $tripStop->stop?->address }}</small><br>
                                    <small class="text-muted">Lat: {{ $tripStop->stop?->latitude ?? 'N/A' }}, Lng: {{ $tripStop->stop?->longitude ?? 'N/A' }}</small>
                                </li>
                            @endforeach
                        </ol>
                    @else
                        <p class="text-muted">No stops have been recorded for this trip.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="card-title">Trip map</h5>
                    <div id="tripMap" style="height: 520px; min-height: 320px;" class="rounded-3 border"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        function whenGoogleMapsReady(callback) {
            if (window.google && window.google.maps) {
                callback();
                return;
            }

            document.addEventListener('google-maps-ready', callback, { once: true });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const tripId = {{ $trip->id }};
            const liveUrl = '{{ route('admin.trips.live', $trip) }}';
            const stops = @json($tripStops);
            let latestLocation = @json($driverLocation);
            const etaLabel = document.getElementById('tripEtaLabel');
            const driverLocationLabel = document.getElementById('driverLocationLabel');
            let map = null;
            let driverMarker = null;
            let routePath = null;

            const setEta = (distanceKm, durationMinutes) => {
                if (etaLabel) {
                    etaLabel.textContent = `${Number(distanceKm || 0).toFixed(1)} km • ${Number(durationMinutes || 0)} min`;
                }
            };

            const estimateDistance = (routeStops) => {
                if (!routeStops || routeStops.length < 2) {
                    return { distanceKm: 0, durationMinutes: 0 };
                }

                const toRad = (value) => (value * Math.PI) / 180;
                const earthRadiusKm = 6371;
                let totalKm = 0;

                for (let i = 0; i < routeStops.length - 1; i++) {
                    const start = routeStops[i];
                    const end = routeStops[i + 1];
                    const lat1 = toRad(parseFloat(start.lat));
                    const lon1 = toRad(parseFloat(start.lng));
                    const lat2 = toRad(parseFloat(end.lat));
                    const lon2 = toRad(parseFloat(end.lng));
                    const deltaLat = lat2 - lat1;
                    const deltaLon = lon2 - lon1;
                    const a = Math.sin(deltaLat / 2) ** 2 + Math.cos(lat1) * Math.cos(lat2) * Math.sin(deltaLon / 2) ** 2;
                    totalKm += earthRadiusKm * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                }

                return {
                    distanceKm: totalKm,
                    durationMinutes: Math.max(20, Math.round((totalKm / 35) * 60)),
                };
            };

            const mapContainer = document.getElementById('tripMap');
            if (!mapContainer || stops.length === 0) {
                if (etaLabel) {
                    etaLabel.textContent = '0.0 km • 0 min';
                }
                return;
            }

            const fallbackMetrics = estimateDistance(stops);
            setEta(fallbackMetrics.distanceKm, fallbackMetrics.durationMinutes);

            if (driverLocationLabel && latestLocation) {
                driverLocationLabel.textContent = `${Number(latestLocation.lat).toFixed(5)}, ${Number(latestLocation.lng).toFixed(5)}`;
            }

            whenGoogleMapsReady(function () {
                const bounds = new google.maps.LatLngBounds();
                map = new google.maps.Map(mapContainer, {
                    zoom: 12,
                    center: latestLocation ? { lat: latestLocation.lat, lng: latestLocation.lng } : { lat: stops[0].lat, lng: stops[0].lng },
                });

                const markers = stops.map((stop, index) => {
                    const marker = new google.maps.Marker({
                        position: { lat: stop.lat, lng: stop.lng },
                        map,
                        label: `${index + 1}`,
                    });

                    bounds.extend(marker.position);
                    const info = new google.maps.InfoWindow({
                        content: `<strong>${stop.name}</strong><br>${stop.address}`,
                    });
                    marker.addListener('click', () => info.open(map, marker));
                    return marker;
                });

                if (driverMarker) {
                    driverMarker.setMap(null);
                    driverMarker = null;
                }
                if (routePath) {
                    routePath.setMap(null);
                    routePath = null;
                }

                if (latestLocation) {
                    driverMarker = new google.maps.Marker({
                        position: { lat: latestLocation.lat, lng: latestLocation.lng },
                        map,
                        title: 'Driver current location',
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: 10,
                            fillColor: '#28a745',
                            fillOpacity: 1,
                            strokeColor: '#ffffff',
                            strokeWeight: 3,
                        },
                    });
                    bounds.extend(driverMarker.getPosition());
                    const driverInfo = new google.maps.InfoWindow({
                        content: `<strong>Driver live location</strong><br>${latestLocation.lat}, ${latestLocation.lng}`,
                    });
                    driverMarker.addListener('click', () => driverInfo.open(map, driverMarker));
                }

                if (stops.length > 1) {
                    const service = new google.maps.DistanceMatrixService();
                    service.getDistanceMatrix({
                        origins: stops.slice(0, -1).map(stop => new google.maps.LatLng(stop.lat, stop.lng)),
                        destinations: stops.slice(1).map(stop => new google.maps.LatLng(stop.lat, stop.lng)),
                        travelMode: google.maps.TravelMode.DRIVING,
                        unitSystem: google.maps.UnitSystem.METRIC,
                    }, (response, status) => {
                        if (status === 'OK' && response && response.rows) {
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

                            const distanceKm = totalDistanceMeters ? totalDistanceMeters / 1000 : fallbackMetrics.distanceKm;
                            const durationMinutes = totalDurationSeconds ? Math.max(20, Math.round(totalDurationSeconds / 60)) : fallbackMetrics.durationMinutes;
                            setEta(distanceKm, durationMinutes);
                        }
                    });

                    routePath = new google.maps.Polyline({
                        path: stops.map(stop => ({ lat: stop.lat, lng: stop.lng })),
                        strokeColor: '#0088ff',
                        strokeOpacity: 0.8,
                        strokeWeight: 4,
                    });
                    routePath.setMap(map);
                }

                if (latestLocation) {
                    const driverPoint = new google.maps.LatLng(latestLocation.lat, latestLocation.lng);
                    bounds.extend(driverPoint);
                }

                map.fitBounds(bounds);
            });

            async function refreshLiveTrip() {
                if (!liveUrl) {
                    return;
                }

                try {
                    const response = await fetch(liveUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    if (!payload || !payload.status) {
                        return;
                    }

                    if (payload.latest_location) {
                        latestLocation = payload.latest_location;
                        if (driverLocationLabel) {
                            driverLocationLabel.textContent = `${Number(payload.latest_location.lat).toFixed(5)}, ${Number(payload.latest_location.lng).toFixed(5)}`;
                        }

                        if (window.google && window.google.maps && map) {
                            const nextPosition = { lat: payload.latest_location.lat, lng: payload.latest_location.lng };
                            if (driverMarker) {
                                driverMarker.setPosition(nextPosition);
                            }
                            if (map && !routePath) {
                                map.setCenter(nextPosition);
                            }
                        }
                    }

                    if (payload.distance_km != null && payload.eta_minutes != null && etaLabel) {
                        etaLabel.textContent = `${Number(payload.distance_km).toFixed(1)} km • ${Number(payload.eta_minutes)} min`;
                    }
                } catch (error) {
                    console.warn('Live trip refresh failed:', error);
                }
            }

            if ({{ $trip->status === 'started' ? 'true' : 'false' }}) {
                setInterval(refreshLiveTrip, 5000);
            }
        });
    </script>
@endsection
