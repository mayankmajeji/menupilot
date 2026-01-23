/**
 * MenuPilot Admin Pages JavaScript
 *
 * @package MenuPilot
 */

/* global jQuery, menupilot */
jQuery(document).ready(function ($) {
  "use strict";

  // Toggle vertical tabs collapse
  $(document).on(
    "click",
    ".mp-collapse-btn[data-mp-toggle='vtabs']",
    function () {
      const $grid = $(this).closest(".mp-2col");
      if ($grid.length) {
        $grid.toggleClass("is-collapsed");
      }
    }
  );

  // Export Page - Enable button when menu is selected
  $(document).on("change", 'input[name="menu_id"]', function () {
    $("#mp-export-btn").prop("disabled", false);
  });

  // Export Page - Handle form submission
  $(document).on("submit", "#mp-export-form", function (e) {
    e.preventDefault();

    const $form = $(this);
    const $button = $("#mp-export-btn");
    const $spinner = $("#mp-export-spinner");
    const $result = $("#mp-export-result");
    const menuId = $('input[name="menu_id"]:checked').val();

    if (!menuId) {
      $result.html(
        '<div class="notice notice-error"><p>Please select a menu to export.</p></div>'
      );
      return;
    }

    // Show spinner
    $button.prop("disabled", true);
    $spinner.addClass("is-active");
    $result.html("");

    // Make REST API request
    $.ajax({
      url: menupilot.restUrl + "/menus/export",
      type: "POST",
      beforeSend: function (xhr) {
        xhr.setRequestHeader("X-WP-Nonce", menupilot.nonce);
      },
      contentType: "application/json",
      data: JSON.stringify({
        menu_id: parseInt(menuId),
      }),
      success(response) {
        if (response.success && response.data) {
          // Create download link
          const jsonString = JSON.stringify(response.data, null, 2);
          const blob = new Blob([jsonString], {
            type: "application/json",
          });
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement("a");
          a.href = url;
          a.download = response.filename || "menu-export.json";
          document.body.appendChild(a);
          a.click();
          window.URL.revokeObjectURL(url);
          document.body.removeChild(a);

          $result.html(
            '<div class="notice notice-success"><p>' +
              response.message +
              "</p></div>"
          );
        } else {
          $result.html(
            '<div class="notice notice-error"><p>' +
              (response.message || "Export failed. Please try again.") +
              "</p></div>"
          );
        }
      },
      error(xhr) {
        let message = "An error occurred. Please try again.";
        if (xhr.responseJSON && xhr.responseJSON.message) {
          message = xhr.responseJSON.message;
        }
        $result.html(
          '<div class="notice notice-error"><p>' + message + "</p></div>"
        );
      },
      complete() {
        $spinner.removeClass("is-active");
        $button.prop("disabled", false);
      },
    });
  });

  // Import Page - Handle file selection
  $(document).on("change", "#mp-menu-file", function () {
    const files = this.files;
    const $button = $("#mp-import-btn");

    if (files && files.length > 0) {
      const fileName = files[0].name;
      $("#mp-file-name").text(fileName);
      $("#mp-file-info").show();
      $button.prop("disabled", false);
      $(".mp-upload-area").addClass("has-file");
    } else {
      $("#mp-file-info").hide();
      $button.prop("disabled", true);
      $(".mp-upload-area").removeClass("has-file");
    }
  });

  // Import Page - Handle form submission (upload & preview)
  $(document).on("submit", "#mp-import-form", function (e) {
    e.preventDefault();

    const $form = $(this);
    const $button = $("#mp-import-btn");
    const $spinner = $("#mp-import-spinner");
    const $result = $("#mp-import-result");
    const fileInput = document.getElementById("mp-menu-file");

    if (!fileInput || !fileInput.files || !fileInput.files[0]) {
      $result.html(
        '<div class="notice notice-error"><p>Please select a file to import.</p></div>'
      );
      return;
    }

    // Show spinner
    $button.prop("disabled", true);
    $spinner.addClass("is-active");
    $result.html("");

    // Read file and fetch mapping options
    const reader = new FileReader();
    reader.onload = function (event) {
      try {
        // Parse JSON
        const importData = JSON.parse(event.target.result);

        // Validate structure
        if (!importData.menu || !importData.menu.name) {
          throw new Error("Invalid menu data structure");
        }

        // Fetch mapping options from REST API
        $.ajax({
          url: menupilot.restUrl + "/menus/mapping-options",
          type: "GET",
          beforeSend: function (xhr) {
            xhr.setRequestHeader("X-WP-Nonce", menupilot.nonce);
          },
          success(mappingResponse) {
            if (mappingResponse.success) {
              // Generate preview HTML with mapping options
              const previewHtml = generateImportPreviewWithMapping(
                importData,
                mappingResponse.options
              );
              showImportModal(previewHtml, importData);
              $result.html("");
            } else {
              throw new Error("Failed to fetch mapping options");
            }
          },
          error() {
            $result.html(
              '<div class="notice notice-error"><p>Failed to fetch mapping options.</p></div>'
            );
          },
          complete() {
            $spinner.removeClass("is-active");
            $button.prop("disabled", false);
          },
        });
      } catch (error) {
        $result.html(
          '<div class="notice notice-error"><p>Invalid JSON file. Please check the file format.</p></div>'
        );
        $spinner.removeClass("is-active");
        $button.prop("disabled", false);
      }
    };

    reader.onerror = function () {
      $result.html(
        '<div class="notice notice-error"><p>Failed to read the file.</p></div>'
      );
      $spinner.removeClass("is-active");
      $button.prop("disabled", false);
    };

    reader.readAsText(fileInput.files[0]);
  });

  // Generate import preview HTML with manual mapping
  function generateImportPreviewWithMapping(importData, mappingOptions) {
    const menu = importData.menu || {};
    const context = importData.export_context || {};

    const originalMenuName = menu.name || "Untitled Menu";
    const menuSlug = menu.slug || "";
    const items = menu.items || [];
    const locations = menu.locations || [];

    const sourceUrl = context.site_url || "";
    const exportedAt = context.exported_at || "";
    const exportedBy = context.exported_by || "";
    const registeredLocations = menupilot.registeredLocations || {};
    const sameSize = sourceUrl === menupilot.siteUrl;
    
    // Apply default menu name pattern
    const defaultPattern = menupilot.defaultMenuNamePattern || '{original_name}';
    const menuName = applyMenuNamePattern(defaultPattern, originalMenuName);

    // Calculate matched items
    let matchedCount = 0;
    items.forEach(function (item) {
      if (item.type === "post_type" && item.slug) {
        // Check if we can find this in mapping options
        const pages = mappingOptions.pages || [];
        const posts = mappingOptions.posts || [];
        const found = pages.some((p) => p.slug === item.slug) || 
                      posts.some((p) => p.slug === item.slug);
        if (found) matchedCount++;
      } else if (item.type === "taxonomy" && item.slug) {
        const taxonomies = mappingOptions.taxonomies || [];
        const found = taxonomies.some((t) => t.slug === item.slug);
        if (found) matchedCount++;
      }
    });

    let html = '<div class="notice notice-info" style="margin: 0 0 20px 0;">';
    html += "<p><strong>Review the import details below before proceeding.</strong> The menu will be created as a new menu.</p>";
    html += "</div>";

    html += '<div class="mp-card">';
    html += "<h3>Import Preview</h3>";
    html += '<table class="widefat"><tbody>';
    html += "<tr><th>Menu Name:</th><td><strong>" + escapeHtml(menuName) + "</strong></td></tr>";
    html += "<tr><th>Total Items:</th><td>" + items.length + "</td></tr>";

    if (sourceUrl) {
      html += "<tr><th>Exported From:</th><td><code>" + escapeHtml(sourceUrl) + "</code>";
      if (sameSize) {
        html += ' <span class="dashicons dashicons-yes-alt" style="color:#46b450;"></span> <em>(Same site)</em>';
      }
      html += "</td></tr>";
    }

    if (exportedAt) {
      const date = new Date(exportedAt);
      html += "<tr><th>Exported At:</th><td>" + date.toLocaleString() + "</td></tr>";
    }

    html += "<tr><th>Items Matched:</th><td>" + matchedCount + " / " + items.length + "</td></tr>";

    html += "</tbody></table></div>";

    // Menu Items Mapping Section
    html += '<div class="mp-card" style="margin-top:20px;">';
    html += "<h3>Menu Items Mapping</h3>";
    html += '<p style="color:#666;">Review and adjust how each menu item will be imported. You can change the mapping, keep items as custom links, or remove items you don\'t want to import.</p>';
    
    html += '<table class="widefat striped mp-mapping-table"><thead><tr>';
    html += "<th style='width: 30%;'>Title</th><th style='width: 15%;'>Type</th><th style='width: 25%;'>Auto Status</th><th style='width: 25%;'>Map To</th><th style='width: 5%; text-align: center;'>Remove</th>";
    html += "</tr></thead><tbody>";

    items.forEach(function (item, index) {
      const indent = item.parent_id > 0 ? "— " : "";
      const itemType = getItemTypeLabel(item);
      const autoStatus = getAutoMatchStatus(item, mappingOptions);
      
      html += "<tr class='mp-mapping-row' data-item-index='" + index + "'>";
      html += "<td>" + escapeHtml(indent + item.title) + "</td>";
      html += "<td>" + itemType + "</td>";
      html += "<td>" + autoStatus.html + "</td>";
      html += "<td>" + generateMappingDropdown(item, index, mappingOptions, autoStatus.matchedId) + "</td>";
      html += "<td style='text-align: center;'><button type='button' class='mp-remove-item' data-item-index='" + index + "' title='Remove this item from import' style='color: #b32d2e; cursor: pointer; border: none; background: transparent; padding: 4px 8px;'><span class='dashicons dashicons-trash'></span></button></td>";
      html += "</tr>";
    });

    html += "</tbody></table></div>";

    // Import Configuration
    html += '<div class="mp-card" style="margin-top:20px;">';
    html += "<h3>Import Configuration</h3>";
    html += '<div id="mp-import-execute-form">';
    html += '<table class="form-table"><tbody>';
    html += '<tr><th scope="row"><label for="mp-import-menu-name">Menu Name:</label></th>';
    html += '<td><input type="text" id="mp-import-menu-name" name="menu_name" value="' + escapeHtml(menuName) + '" class="regular-text" required />';
    html += '<p class="description">Enter a name for the imported menu. The default pattern from Settings has been applied, but you can change it.</p></td></tr>';
    html += '<tr><th scope="row"><label for="mp-import-location">Assign to Location:</label></th>';
    html += '<td><select id="mp-import-location" name="location"><option value="">— Do not assign —</option>';

    for (const [locationId, locationName] of Object.entries(registeredLocations)) {
      html += '<option value="' + escapeHtml(locationId) + '">' + escapeHtml(locationName) + "</option>";
    }

    html += "</select>";
    html += '<p class="description">Optionally assign this menu to a theme location.</p></td></tr>';
    html += "</tbody></table>";
    html += '<input type="hidden" id="mp-import-data" name="import_data" value="" />';
    html += "</div></div>";

    return html;
  }

  // Helper: Get item type label
  function getItemTypeLabel(item) {
    if (item.type === "custom") return "Custom Link";
    if (item.type === "post_type") {
      if (item.object === "page") return "Page";
      if (item.object === "post") return "Post";
      return item.object;
    }
    if (item.type === "taxonomy") {
      if (item.object === "category") return "Category";
      return item.object;
    }
    return item.type;
  }

  // Helper: Get auto-match status
  function getAutoMatchStatus(item, mappingOptions) {
    if (item.type === "custom") {
      return {
        html: '<span class="dashicons dashicons-admin-links" style="color:#82878c;"></span> <strong>Custom Link</strong><br><small style="color:#666;">Custom link - will be imported as-is</small>',
        matchedId: null,
      };
    }

    if (item.type === "post_type" && item.slug) {
      const pages = mappingOptions.pages || [];
      const posts = mappingOptions.posts || [];
      const allPosts = item.object === "page" ? pages : posts;
      
      const matched = allPosts.find((p) => p.slug === item.slug);
      if (matched) {
        return {
          html: '<span class="dashicons dashicons-yes-alt" style="color:#46b450;"></span> <strong>Matched</strong><br><small style="color:#666;">Matched: ' + escapeHtml(matched.title) + " (ID: " + matched.id + ")</small>",
          matchedId: matched.id,
        };
      }
    }

    if (item.type === "taxonomy" && item.slug) {
      const taxonomies = mappingOptions.taxonomies || [];
      const matched = taxonomies.find((t) => t.slug === item.slug);
      if (matched) {
        return {
          html: '<span class="dashicons dashicons-yes-alt" style="color:#46b450;"></span> <strong>Matched</strong><br><small style="color:#666;">Matched: ' + escapeHtml(matched.title) + " (ID: " + matched.id + ")</small>",
          matchedId: matched.id,
        };
      }
    }

    return {
      html: '<span class="dashicons dashicons-warning" style="color:#f0b849;"></span> <strong>Not Found</strong><br><small style="color:#666;">Will be converted to custom link</small>',
      matchedId: null,
    };
  }

  // Helper: Generate mapping dropdown
  function generateMappingDropdown(item, index, mappingOptions, matchedId) {
    let html = '<select class="mp-mapping-select" data-item-index="' + index + '" style="width:100%;">';
    
    // Keep as Custom Link option
    const isCustomSelected = item.type === "custom" || !matchedId;
    html += '<option value="custom:0"' + (isCustomSelected ? " selected" : "") + ">Keep as Custom Link</option>";

    // Add Posts optgroup
    const posts = mappingOptions.posts || [];
    if (posts.length > 0) {
      html += '<optgroup label="Posts">';
      posts.forEach(function (post) {
        const selected = matchedId === post.id && item.object === "post" ? " selected" : "";
        html += '<option value="post:' + post.id + '"' + selected + ">" + escapeHtml(post.title) + " (ID: " + post.id + ")</option>";
      });
      html += "</optgroup>";
    }

    // Add Pages optgroup
    const pages = mappingOptions.pages || [];
    if (pages.length > 0) {
      html += '<optgroup label="Pages">';
      pages.forEach(function (page) {
        const selected = matchedId === page.id && item.object === "page" ? " selected" : "";
        html += '<option value="page:' + page.id + '"' + selected + ">" + escapeHtml(page.title) + " (ID: " + page.id + ")</option>";
      });
      html += "</optgroup>";
    }

    // Add Categories optgroup
    const taxonomies = mappingOptions.taxonomies || [];
    if (taxonomies.length > 0) {
      html += '<optgroup label="Categories">';
      taxonomies.forEach(function (tax) {
        const selected = matchedId === tax.id && item.object === "category" ? " selected" : "";
        html += '<option value="category:' + tax.id + '"' + selected + ">" + escapeHtml(tax.title) + " (ID: " + tax.id + ")</option>";
      });
      html += "</optgroup>";
    }

    html += "</select>";
    
    if (matchedId) {
      html += '<br><small style="color:#46b450;"><span class="dashicons dashicons-yes-alt"></span> Auto-matched - you can change this if needed</small>';
    } else {
      html += '<br><small style="color:#666;font-style:italic;">Originally a custom link - you can map it to content if needed</small>';
    }

    return html;
  }

  // Generate import preview HTML client-side (old function - keeping for backward compatibility)
  function generateImportPreview(importData) {
    const menu = importData.menu || {};
    const context = importData.export_context || {};

    const originalMenuName = menu.name || "Untitled Menu";
    const menuSlug = menu.slug || "";
    const items = menu.items || [];
    
    // Apply default menu name pattern
    const defaultPattern = menupilot.defaultMenuNamePattern || '{original_name}';
    const menuName = applyMenuNamePattern(defaultPattern, originalMenuName);
    const locations = menu.locations || [];

    const sourceUrl = context.site_url || "";
    const exportedAt = context.exported_at || "";
    const registeredLocations = menupilot.registeredLocations || {};

    let html = '<div class="mp-card">';
    html += "<h3>Import Preview</h3>";
    html += '<table class="widefat"><tbody>';
    html +=
      "<tr><th>Menu Name:</th><td><strong>" +
      escapeHtml(menuName) +
      "</strong></td></tr>";
    html +=
      "<tr><th>Menu Slug:</th><td><code>" +
      escapeHtml(menuSlug) +
      "</code></td></tr>";
    html += "<tr><th>Total Items:</th><td>" + items.length + "</td></tr>";

    if (sourceUrl) {
      html +=
        "<tr><th>Source Site:</th><td><code>" +
        escapeHtml(sourceUrl) +
        "</code></td></tr>";
    }

    html +=
      "<tr><th>Destination Site:</th><td><code>" +
      menupilot.siteUrl +
      "</code></td></tr>";

    if (exportedAt) {
      const date = new Date(exportedAt);
      html +=
        "<tr><th>Exported At:</th><td>" + date.toLocaleString() + "</td></tr>";
    }

    if (locations.length > 0) {
      html +=
        "<tr><th>Source Locations:</th><td><code>" +
        locations.join(", ") +
        "</code></td></tr>";
    }

    html += "</tbody></table></div>";

    // Import configuration
    html += '<div class="mp-card" style="margin-top:20px;">';
    html += "<h3>Import Configuration</h3>";
    html += "<p>Configure how this menu will be imported:</p>";
    html += '<div id="mp-import-execute-form">';
    html += '<table class="form-table"><tbody>';
    html +=
      '<tr><th scope="row"><label for="mp-import-menu-name">Menu Name:</label></th>';
    html +=
      '<td><input type="text" id="mp-import-menu-name" name="menu_name" value="' +
      escapeHtml(menuName) +
      '" class="regular-text" required />';
    html +=
      '<p class="description">Enter a name for the imported menu. The default pattern from Settings has been applied, but you can change it.</p></td></tr>';
    html +=
      '<tr><th scope="row"><label for="mp-import-location">Assign to Location:</label></th>';
    html +=
      '<td><select id="mp-import-location" name="location"><option value="">— Do not assign —</option>';

    for (const [locationId, locationName] of Object.entries(
      registeredLocations
    )) {
      html +=
        '<option value="' +
        escapeHtml(locationId) +
        '">' +
        escapeHtml(locationName) +
        "</option>";
    }

    html += "</select>";
    html +=
      '<p class="description">Optionally assign this menu to a theme location.</p></td></tr>';
    html += "</tbody></table>";
    html +=
      '<input type="hidden" id="mp-import-data" name="import_data" value="" />';
    html += "</div></div>";

    // Menu items preview
    html += '<div class="mp-card" style="margin-top:20px;">';
    html += "<h3>Menu Items Preview</h3>";
    html += '<table class="widefat striped"><thead><tr>';
    html += "<th>Title</th><th>Type</th><th>Object</th><th>URL</th>";
    html += "</tr></thead><tbody>";

    items.forEach(function (item) {
      const indent = item.parent_id > 0 ? "— " : "";
      html += "<tr>";
      html += "<td>" + escapeHtml(indent + item.title) + "</td>";
      html += "<td><code>" + escapeHtml(item.type) + "</code></td>";
      html += "<td><code>" + escapeHtml(item.object) + "</code></td>";
      html += "<td><small>" + escapeHtml(item.url) + "</small></td>";
      html += "</tr>";
    });

    html += "</tbody></table></div>";

    return html;
  }

  // Helper function to apply menu name pattern
  function applyMenuNamePattern(pattern, originalName) {
    if (!pattern || !originalName) {
      return originalName || "Untitled Menu";
    }
    
    const date = new Date();
    const dateStr = date.getFullYear() + '-' + 
                   String(date.getMonth() + 1).padStart(2, '0') + '-' + 
                   String(date.getDate()).padStart(2, '0');
    const timeStr = String(date.getHours()).padStart(2, '0') + 
                    String(date.getMinutes()).padStart(2, '0') + 
                    String(date.getSeconds()).padStart(2, '0');
    
    return pattern
      .replace(/{original_name}/g, originalName)
      .replace(/{date}/g, dateStr)
      .replace(/{time}/g, timeStr);
  }

  // Helper function to escape HTML
  function escapeHtml(text) {
    const map = {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#039;",
    };
    return String(text).replace(/[&<>"']/g, function (m) {
      return map[m];
    });
  }

  // Import Modal Functions
  function showImportModal(html, data) {
    // Create modal if it doesn't exist
    if (!$("#mp-import-modal").length) {
      $("body").append(`
        <div id="mp-import-modal" class="mp-modal-overlay">
          <div class="mp-modal">
            <div class="mp-modal-header">
              <h2>Import Menu Preview</h2>
              <button type="button" class="mp-modal-close" aria-label="Close">
                <span class="dashicons dashicons-no-alt"></span>
              </button>
            </div>
            <div class="mp-modal-body"></div>
            <div class="mp-modal-footer">
              <button type="button" class="button" id="mp-modal-cancel">Cancel</button>
              <button type="button" class="button button-primary" id="mp-modal-import">Import Menu</button>
              <span class="spinner"></span>
            </div>
          </div>
        </div>
      `);
    }

    // Update modal content
    $("#mp-import-modal .mp-modal-body").html(html);

    // Store import data (wait for DOM to be ready)
    setTimeout(function () {
      if ($("#mp-import-data").length) {
        $("#mp-import-data").val(JSON.stringify(data));
      } else {
        console.error("Import data field not found");
      }
    }, 100);

    // Show modal
    $("#mp-import-modal").addClass("is-active");
    $("body").addClass("modal-open");
  }

  function closeImportModal() {
    $("#mp-import-modal").removeClass("is-active");
    $("body").removeClass("modal-open");
  }

  // Close modal on overlay click, close button, or cancel
  $(document).on("click", "#mp-import-modal", function (e) {
    if (e.target === this) {
      closeImportModal();
    }
  });

  $(document).on("click", ".mp-modal-close, #mp-modal-cancel", function () {
    closeImportModal();
  });

  // Close modal on ESC key
  $(document).on("keydown", function (e) {
    if (e.key === "Escape" && $("#mp-import-modal").hasClass("is-active")) {
      closeImportModal();
    }
  });

  // Handle Enter key in input fields to trigger import
  $(document).on("keypress", "#mp-import-menu-name", function (e) {
    if (e.which === 13) {
      e.preventDefault();
      $("#mp-modal-import").trigger("click");
    }
  });

  // Show remove confirmation modal
  function showRemoveConfirmModal(itemTitle, onConfirm) {
    // Create modal HTML if it doesn't exist
    if ($("#mp-confirm-modal").length === 0) {
      const modalHtml = `
        <div id="mp-confirm-modal" class="mp-confirm-overlay">
          <div class="mp-confirm-dialog">
            <div class="mp-confirm-header">
              <h3>Confirm Removal</h3>
            </div>
            <div class="mp-confirm-body">
              <p class="mp-confirm-message"></p>
            </div>
            <div class="mp-confirm-footer">
              <button type="button" class="button" id="mp-confirm-cancel">Cancel</button>
              <button type="button" class="button button-primary" id="mp-confirm-yes">Remove</button>
            </div>
          </div>
        </div>
      `;
      $("body").append(modalHtml);
    }

    // Store the callback
    $("#mp-confirm-modal").data("onConfirm", onConfirm);

    // Set the message
    $("#mp-confirm-modal .mp-confirm-message").html(
      'Are you sure you want to remove <strong>"' + escapeHtml(itemTitle) + '"</strong> from the import?'
    );

    // Show modal
    $("#mp-confirm-modal").addClass("is-active");
  }

  // Close remove confirmation modal
  function closeRemoveConfirmModal() {
    $("#mp-confirm-modal").removeClass("is-active");
    $("#mp-confirm-modal").removeData("onConfirm");
  }

  // Confirm removal
  $(document).on("click", "#mp-confirm-yes", function () {
    const onConfirm = $("#mp-confirm-modal").data("onConfirm");
    if (typeof onConfirm === "function") {
      onConfirm();
    }
    closeRemoveConfirmModal();
  });

  // Cancel removal
  $(document).on("click", "#mp-confirm-cancel", function () {
    closeRemoveConfirmModal();
  });

  // Close on overlay click
  $(document).on("click", "#mp-confirm-modal", function (e) {
    if (e.target === this) {
      closeRemoveConfirmModal();
    }
  });

  // Close on Escape key
  $(document).on("keydown", function (e) {
    if (e.key === "Escape" && $("#mp-confirm-modal").hasClass("is-active")) {
      closeRemoveConfirmModal();
    }
  });

  // Import Modal - Handle item removal
  $(document).on("click", ".mp-remove-item", function (e) {
    e.preventDefault();
    const $button = $(this);
    const $row = $button.closest(".mp-mapping-row");
    const itemIndex = $button.data("item-index");
    const itemTitle = $row.find("td:first").text().trim();

    // Show confirmation modal
    showRemoveConfirmModal(itemTitle, function() {
      // User confirmed - proceed with removal
      $row.addClass("mp-item-removed");
      $row.css({
        opacity: "0.4",
        textDecoration: "line-through",
        backgroundColor: "#fee"
      });
      
      // Replace remove button with undo option
      $button.replaceWith(
        '<button type="button" class="mp-undo-remove" data-item-index="' + 
        itemIndex + 
        '" title="Undo removal" style="color: #46b450; cursor: pointer; border: none; background: transparent; padding: 4px 8px;"><span class="dashicons dashicons-undo"></span></button>'
      );
      
      // Mark row as removed (will be filtered out during import)
      $row.attr("data-removed", "true");
    });
  });

  // Import Modal - Handle undo removal
  $(document).on("click", ".mp-undo-remove", function (e) {
    e.preventDefault();
    const $button = $(this);
    const $row = $button.closest(".mp-mapping-row");
    const itemIndex = $button.data("item-index");

    // Restore visual appearance
    $row.removeClass("mp-item-removed");
    $row.css({
      opacity: "1",
      textDecoration: "none",
      backgroundColor: ""
    });
    
    // Replace undo button with remove button
    $button.replaceWith(
      '<button type="button" class="mp-remove-item" data-item-index="' + 
      itemIndex + 
      '" title="Remove this item from import" style="color: #b32d2e; cursor: pointer; border: none; background: transparent; padding: 4px 8px;"><span class="dashicons dashicons-trash"></span></button>'
    );
    
    // Remove the removed marker
    $row.removeAttr("data-removed");
  });

  // Import Modal - Handle import execution
  $(document).on("click", "#mp-modal-import", function (e) {
    e.preventDefault();
    e.stopPropagation();

    console.log("Import button clicked");

    const $button = $(this);
    const $spinner = $("#mp-import-modal .mp-modal-footer .spinner");
    const $result = $("#mp-import-result");
    const menuName = $("#mp-import-menu-name").val();
    const location = $("#mp-import-location").val();
    const importData = $("#mp-import-data").val();

    console.log("Menu name:", menuName);
    console.log("Location:", location);
    console.log("Import data length:", importData ? importData.length : 0);

    if (!menuName) {
      alert("Please enter a menu name.");
      return false;
    }

    if (!importData) {
      alert("Import data is missing. Please try uploading the file again.");
      closeImportModal();
      return false;
    }

    // Collect custom mappings from dropdowns
    const customMappings = {};
    const removedItems = [];
    
    $(".mp-mapping-row").each(function () {
      const $row = $(this);
      const itemIndex = $row.data("item-index");
      
      // Check if item is marked for removal
      if ($row.attr("data-removed") === "true") {
        removedItems.push(itemIndex);
        return; // Skip this item
      }
      
      // Collect mapping for non-removed items
      const $select = $row.find(".mp-mapping-select");
      const mappingValue = $select.val();
      if (mappingValue && mappingValue !== "custom:0") {
        customMappings[itemIndex] = mappingValue;
      }
    });

    console.log("Custom mappings:", customMappings);
    console.log("Removed items:", removedItems);

    // Show spinner
    $button.prop("disabled", true);
    $("#mp-modal-cancel").prop("disabled", true);
    $spinner.addClass("is-active");
    $result.html("");

    // Parse import data
    let menuData;
    try {
      menuData = JSON.parse(importData);
      
      // Apply custom mappings and remove excluded items from menu items
      if (menuData.menu && menuData.menu.items) {
        // Filter out removed items
        const originalItems = menuData.menu.items;
        menuData.menu.items = originalItems.filter(function(item, index) {
          return removedItems.indexOf(index) === -1;
        });
        
        // Apply custom mappings to remaining items (adjust indices)
        menuData.menu.items.forEach(function(item, newIndex) {
          // Find original index
          const originalIndex = originalItems.indexOf(item);
          
          if (customMappings[originalIndex]) {
            const [type, id] = customMappings[originalIndex].split(":");
            item.custom_mapping = {
              type: type,
              id: parseInt(id)
            };
          }
        });
        
        // Update parent_id references for remaining items
        // If parent was removed, make child a top-level item
        const removedIds = removedItems.map(function(idx) {
          return originalItems[idx] ? originalItems[idx].id : null;
        }).filter(Boolean);
        
        menuData.menu.items.forEach(function(item) {
          if (item.parent_id && removedIds.indexOf(item.parent_id) !== -1) {
            item.parent_id = 0; // Make it a top-level item
          }
        });
      }
    } catch (error) {
      alert("Invalid import data. Please try uploading the file again.");
      closeImportModal();
      return false;
    }

    // Make REST API request
    $.ajax({
      url: menupilot.restUrl + "/menus/import",
      type: "POST",
      beforeSend: function (xhr) {
        xhr.setRequestHeader("X-WP-Nonce", menupilot.nonce);
      },
      contentType: "application/json",
      data: JSON.stringify({
        menu_name: menuName,
        menu_data: menuData,
        location: location,
      }),
      success(response) {
        if (response.success) {
          closeImportModal();
          $result.html(
            '<div class="notice notice-success"><p>' +
              response.message +
              ' <a href="' +
              response.edit_url +
              '">Edit menu</a></p></div>'
          );
          // Reset form
          $("#mp-menu-file").val("");
          $("#mp-file-info").hide();
          $("#mp-import-btn").prop("disabled", true);
          $(".mp-upload-area").removeClass("has-file");

          // Scroll to result
          $("html, body").animate(
            { scrollTop: $result.offset().top - 100 },
            300
          );
        } else {
          $result.html(
            '<div class="notice notice-error"><p>' +
              (response.message || "Import failed. Please try again.") +
              "</p></div>"
          );
        }
      },
      error(xhr) {
        let message = "An error occurred during import. Please try again.";
        if (xhr.responseJSON && xhr.responseJSON.message) {
          message = xhr.responseJSON.message;
        }
        $result.html(
          '<div class="notice notice-error"><p>' + message + "</p></div>"
        );
      },
      complete() {
        $spinner.removeClass("is-active");
        $button.prop("disabled", false);
        $("#mp-modal-cancel").prop("disabled", false);
      },
    });
  });

  // Drag and drop support for file upload
  const $uploadArea = $(".mp-upload-area");
  if ($uploadArea.length) {
    $uploadArea
      .on("dragover", function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass("dragover");
      })
      .on("dragleave", function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass("dragover");
      })
      .on("drop", function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass("dragover");

        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
          document.getElementById("mp-menu-file").files = files;
          $("#mp-menu-file").trigger("change");
        }
      });
  }

  // Settings Page - Menu Name Pattern Field Handler
  function initMenuNamePatternField(fieldId, customFieldId) {
    var customRadio = document.getElementById(fieldId + '_custom_radio');
    var customInput = document.getElementById(customFieldId);
    if (!customRadio || !customInput) return;

    var otherRadios = document.querySelectorAll('input[name="menupilot_settings[' + fieldId + ']"]:not(#' + fieldId + '_custom_radio)');
    var form = customInput.closest('form');

    function updateCustomValue() {
      if (customRadio.checked) {
        customInput.disabled = false;
        customInput.required = true;
      } else {
        customInput.disabled = true;
        customInput.required = false;
      }
    }

    // Update main field value when custom is selected and form is submitted
    if (form) {
      form.addEventListener('submit', function() {
        if (customRadio.checked && customInput.value) {
          // Create a hidden input to set the main field value to the custom pattern
          var hiddenInput = document.createElement('input');
          hiddenInput.type = 'hidden';
          hiddenInput.name = 'menupilot_settings[' + fieldId + ']';
          hiddenInput.value = customInput.value;
          form.appendChild(hiddenInput);
        }
      });
    }

    customRadio.addEventListener('change', updateCustomValue);
    otherRadios.forEach(function(radio) {
      radio.addEventListener('change', updateCustomValue);
    });

    // Initialize on page load
    updateCustomValue();
  }

  // Settings Page - Export Filename Pattern Field Handler
  function initExportFilenamePatternField(fieldId, customFieldId) {
    var customRadio = document.getElementById(fieldId + '_custom_radio');
    var customInput = document.getElementById(customFieldId);
    if (!customRadio || !customInput) return;

    var otherRadios = document.querySelectorAll('input[name="menupilot_settings[' + fieldId + ']"]:not(#' + fieldId + '_custom_radio)');
    var form = customInput.closest('form');

    function updateCustomValue() {
      if (customRadio.checked) {
        customInput.disabled = false;
        customInput.required = true;
      } else {
        customInput.disabled = true;
        customInput.required = false;
      }
    }

    // Update main field value when custom is selected and form is submitted
    if (form) {
      form.addEventListener('submit', function() {
        if (customRadio.checked && customInput.value) {
          // Create a hidden input to set the main field value to the custom pattern
          var hiddenInput = document.createElement('input');
          hiddenInput.type = 'hidden';
          hiddenInput.name = 'menupilot_settings[' + fieldId + ']';
          hiddenInput.value = customInput.value;
          form.appendChild(hiddenInput);
        }
      });
    }

    customRadio.addEventListener('change', updateCustomValue);
    otherRadios.forEach(function(radio) {
      radio.addEventListener('change', updateCustomValue);
    });

    // Initialize on page load
    updateCustomValue();
  }

  // FAQ Accordion Handler
  function initFAQAccordion() {
    var questions = document.querySelectorAll('.faq-question');
    var answers = document.querySelectorAll('.faq-answer');
    if (questions.length === 0) return;

    questions.forEach(function(q, idx) {
      q.addEventListener('click', function() {
        var expanded = q.getAttribute('aria-expanded') === 'true';
        // Collapse all
        questions.forEach(function(qq, i) {
          qq.setAttribute('aria-expanded', 'false');
          answers[i].style.display = 'none';
        });
        // Expand this one if it was not already open
        if (!expanded) {
          q.setAttribute('aria-expanded', 'true');
          answers[idx].style.display = 'block';
        }
      });
      q.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
          q.click();
          e.preventDefault();
        }
      });
      // Start collapsed
      answers[idx].style.display = 'none';
    });
  }

  // Help Page - Copy System Info Handler
  function initCopySystemInfo(pluginVer, wpVersion, phpVersion, memoryLimit) {
    var btn = document.getElementById('mp-copy-system-info');
    if (!btn) return;

    btn.addEventListener('click', function() {
      var info = [
        'MenuPilot: v' + pluginVer,
        'WordPress: v' + wpVersion,
        'PHP: v' + phpVersion,
        'Memory Limit: ' + memoryLimit
      ].join('\n');

      function showCopied() {
        var msg = document.getElementById('mp-copy-system-info-msg');
        if (msg) {
          msg.style.display = 'inline';
          setTimeout(function() {
            msg.style.display = 'none';
          }, 1500);
        }
      }

      function fallbackCopy(text) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.position = 'absolute';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        try {
          var ok = document.execCommand('copy');
          document.body.removeChild(ta);
          if (ok) showCopied();
        } catch (e) {
          document.body.removeChild(ta);
        }
      }

      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(info).then(showCopied).catch(function() {
          fallbackCopy(info);
        });
      } else {
        fallbackCopy(info);
      }
    });
  }

  // Initialize functions based on data attributes or page context
  if (typeof menupilot !== 'undefined' && menupilot.initFunctions) {
    if (menupilot.initFunctions.menuNamePattern) {
      initMenuNamePatternField(
        menupilot.initFunctions.menuNamePattern.fieldId,
        menupilot.initFunctions.menuNamePattern.customFieldId
      );
    }
    if (menupilot.initFunctions.exportFilenamePattern) {
      initExportFilenamePatternField(
        menupilot.initFunctions.exportFilenamePattern.fieldId,
        menupilot.initFunctions.exportFilenamePattern.customFieldId
      );
    }
    if (menupilot.initFunctions.faqAccordion) {
      initFAQAccordion();
    }
    if (menupilot.initFunctions.copySystemInfo) {
      initCopySystemInfo(
        menupilot.initFunctions.copySystemInfo.pluginVer,
        menupilot.initFunctions.copySystemInfo.wpVersion,
        menupilot.initFunctions.copySystemInfo.phpVersion,
        menupilot.initFunctions.copySystemInfo.memoryLimit
      );
    }
  }
});
