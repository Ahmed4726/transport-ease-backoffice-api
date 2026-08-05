<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places"></script>

<script>

document.addEventListener("DOMContentLoaded", function () {

    const sidebar = document.getElementById('sidebar');

    const toggle = document.getElementById('sidebarToggle');

    if (toggle) {

        toggle.addEventListener('click', function () {

            sidebar.classList.toggle('show');

        });

    }

    const searchInput = document.getElementById('route-stop-search');
    const addressInput = document.getElementById('route-stop-address');
    const latInput = document.getElementById('route-stop-latitude');
    const lngInput = document.getElementById('route-stop-longitude');
    const placeIdInput = document.getElementById('route-stop-place-id');
    const mapElement = document.getElementById('route-stop-map');

    if (searchInput && addressInput && latInput && lngInput && placeIdInput && mapElement && google) {
        const map = new google.maps.Map(mapElement, {
            center: { lat: 24.8607, lng: 67.0011 },
            zoom: 8,
        });

        const autocomplete = new google.maps.places.Autocomplete(searchInput);
        autocomplete.bindTo('bounds', map);

        const marker = new google.maps.Marker({
            map: map,
            draggable: true,
        });

        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();
            if (!place.geometry) {
                return;
            }

            map.setCenter(place.geometry.location);
            map.setZoom(14);
            marker.setPosition(place.geometry.location);
            marker.setVisible(true);

            addressInput.value = place.formatted_address || '';
            latInput.value = place.geometry.location.lat();
            lngInput.value = place.geometry.location.lng();
            placeIdInput.value = place.place_id || '';
        });

        google.maps.event.addListener(map, 'click', function (event) {
            const position = event.latLng;
            marker.setPosition(position);
            marker.setVisible(true);
            latInput.value = position.lat();
            lngInput.value = position.lng();
            placeIdInput.value = '';
            addressInput.value = '';
        });
    }

    const citySearchInput = document.getElementById('city-search');
    const cityLatInput = document.getElementById('city-latitude');
    const cityLngInput = document.getElementById('city-longitude');
    const cityMapElement = document.getElementById('city-map');

    if (citySearchInput && cityLatInput && cityLngInput && cityMapElement && google) {
        const initialLat = parseFloat(cityLatInput.value) || 24.8607;
        const initialLng = parseFloat(cityLngInput.value) || 67.0011;

        const cityMap = new google.maps.Map(cityMapElement, {
            center: { lat: initialLat, lng: initialLng },
            zoom: 8,
        });

        const cityAutocomplete = new google.maps.places.Autocomplete(citySearchInput);
        cityAutocomplete.bindTo('bounds', cityMap);

        const cityMarker = new google.maps.Marker({
            map: cityMap,
            draggable: true,
        });

        if (cityLatInput.value && cityLngInput.value) {
            const position = { lat: parseFloat(cityLatInput.value), lng: parseFloat(cityLngInput.value) };
            cityMarker.setPosition(position);
            cityMarker.setVisible(true);
            cityMap.setCenter(position);
            cityMap.setZoom(10);
        }

        cityAutocomplete.addListener('place_changed', function () {
            const place = cityAutocomplete.getPlace();
            if (!place.geometry) {
                return;
            }

            cityMap.setCenter(place.geometry.location);
            cityMap.setZoom(14);
            cityMarker.setPosition(place.geometry.location);
            cityMarker.setVisible(true);

            cityLatInput.value = place.geometry.location.lat();
            cityLngInput.value = place.geometry.location.lng();
        });

        google.maps.event.addListener(cityMap, 'click', function (event) {
            const position = event.latLng;
            cityMarker.setPosition(position);
            cityMarker.setVisible(true);
            cityLatInput.value = position.lat();
            cityLngInput.value = position.lng();
        });
    }

    const cityStopSearch = document.getElementById('city-stop-search');
    const cityStopAddress = document.getElementById('city-stop-address');
    const cityStopLat = document.getElementById('city-stop-latitude');
    const cityStopLng = document.getElementById('city-stop-longitude');
    const cityStopPlace = document.getElementById('city-stop-place-id');
    const cityStopMapEl = document.getElementById('city-stop-map');

    if (cityStopSearch && cityStopAddress && cityStopLat && cityStopLng && cityStopPlace && cityStopMapEl && google) {
        const map = new google.maps.Map(cityStopMapEl, {
            center: { lat: 24.8607, lng: 67.0011 },
            zoom: 12,
        });

        const stopAutocomplete = new google.maps.places.Autocomplete(cityStopSearch);
        stopAutocomplete.bindTo('bounds', map);

        const stopMarker = new google.maps.Marker({ map: map, draggable: true });

        stopAutocomplete.addListener('place_changed', function () {
            const place = stopAutocomplete.getPlace();
            if (!place.geometry) return;
            map.setCenter(place.geometry.location);
            map.setZoom(15);
            stopMarker.setPosition(place.geometry.location);
            stopMarker.setVisible(true);

            cityStopAddress.value = place.formatted_address || '';
            cityStopLat.value = place.geometry.location.lat();
            cityStopLng.value = place.geometry.location.lng();
            cityStopPlace.value = place.place_id || '';
        });

        google.maps.event.addListener(map, 'click', function (event) {
            const pos = event.latLng;
            stopMarker.setPosition(pos);
            stopMarker.setVisible(true);
            cityStopLat.value = pos.lat();
            cityStopLng.value = pos.lng();
            cityStopPlace.value = '';
            cityStopAddress.value = '';
        });
    }

});

</script>
