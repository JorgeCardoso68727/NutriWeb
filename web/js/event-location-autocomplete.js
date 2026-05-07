// Location autocomplete using Photon (OpenStreetMap-based)
function initEventLocationAutocomplete() {
  const PHOTON_API = "https://photon.komoot.io/api/";

  document.querySelectorAll(".location-autocomplete").forEach(function (input) {
    if (input._autocompleteAttached) return;

    // Create dropdown container for suggestions
    var suggestionsContainer = document.createElement("ul");
    suggestionsContainer.className = "location-suggestions";
    suggestionsContainer.style.cssText = `
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background: white;
      border: 1px solid #dee2e6;
      border-top: none;
      border-radius: 0 0 8px 8px;
      max-height: 200px;
      overflow-y: auto;
      list-style: none;
      margin: 0;
      padding: 0;
      display: none;
      z-index: 1000;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    `;

    // Position relative wrapper
    var wrapper = document.createElement("div");
    wrapper.style.position = "relative";
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);
    wrapper.appendChild(suggestionsContainer);

    // Debounce timer
    var debounceTimer;

    // Handle input
    input.addEventListener("input", function (e) {
      clearTimeout(debounceTimer);
      var query = this.value.trim();

      if (query.length < 2) {
        suggestionsContainer.style.display = "none";
        return;
      }

      debounceTimer = setTimeout(function () {
        fetch(
          PHOTON_API +
            "?q=" +
            encodeURIComponent(query) +
            "&limit=5&bbox=-10.5,36.7,-5.8,42.2",
        )
          .then((r) => r.json())
          .then((data) => {
            suggestionsContainer.innerHTML = "";
            if (!data.features || data.features.length === 0) {
              suggestionsContainer.style.display = "none";
              return;
            }
            data.features.forEach(function (feature) {
              var name = feature.properties.name || "";
              var city = feature.properties.city || "";
              var country = feature.properties.country || "";
              var displayText =
                [name, city].filter(Boolean).join(", ") || country;

              var li = document.createElement("li");
              li.style.cssText = `
                padding: 8px 12px;
                cursor: pointer;
                border-bottom: 1px solid #f0f0f0;
              `;
              li.textContent = displayText;
              li.addEventListener("click", function () {
                input.value = displayText;
                try {
                  input.dataset.place = JSON.stringify(feature);
                } catch (e) {
                  /* ignore */
                }
                suggestionsContainer.style.display = "none";
              });
              li.addEventListener("mouseenter", function () {
                li.style.backgroundColor = "#f8f9fa";
              });
              li.addEventListener("mouseleave", function () {
                li.style.backgroundColor = "transparent";
              });
              suggestionsContainer.appendChild(li);
            });
            suggestionsContainer.style.display = "block";
          })
          .catch((e) => console.warn("Photon autocomplete error:", e));
      }, 300);
    });

    // Hide on blur
    input.addEventListener("blur", function () {
      setTimeout(() => {
        suggestionsContainer.style.display = "none";
      }, 200);
    });

    input._autocompleteAttached = true;
  });
}

// Init when DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initEventLocationAutocomplete);
} else {
  initEventLocationAutocomplete();
}
