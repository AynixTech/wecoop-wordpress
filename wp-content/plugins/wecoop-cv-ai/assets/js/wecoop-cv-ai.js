(function () {
  "use strict";

  function rowTemplate(type) {
    if (type === "languages") {
      return [
        '<div class="wecoop-cv-ai__row" data-row>',
        '<input type="text" data-field="language" placeholder="Idioma" required>',
        '<input type="text" data-field="level" placeholder="Nivel" required>',
        '<button type="button" data-remove-row>Eliminar</button>',
        "</div>",
      ].join("");
    }

    if (type === "experience") {
      return [
        '<div class="wecoop-cv-ai__row" data-row>',
        '<input type="text" data-field="role" placeholder="Rol" required>',
        '<input type="text" data-field="company" placeholder="Empresa">',
        '<input type="text" data-field="country" placeholder="Pais">',
        '<input type="date" data-field="startDate">',
        '<input type="date" data-field="endDate">',
        '<textarea data-field="description" placeholder="Descripcion"></textarea>',
        '<button type="button" data-remove-row>Eliminar</button>',
        "</div>",
      ].join("");
    }

    return [
      '<div class="wecoop-cv-ai__row" data-row>',
      '<input type="text" data-field="title" placeholder="Titulo" required>',
      '<input type="text" data-field="institution" placeholder="Institucion">',
      '<input type="text" data-field="country" placeholder="Pais">',
      '<input type="date" data-field="startDate">',
      '<input type="date" data-field="endDate">',
      '<textarea data-field="description" placeholder="Descripcion"></textarea>',
      '<button type="button" data-remove-row>Eliminar</button>',
      "</div>",
    ].join("");
  }

  function collectRows(container) {
    return Array.from(container.querySelectorAll("[data-row]")).map(function (row) {
      var fields = Array.from(row.querySelectorAll("[data-field]"));
      var obj = {};
      fields.forEach(function (el) {
        var key = el.getAttribute("data-field");
        obj[key] = (el.value || "").trim();
      });
      return obj;
    }).filter(function (item) {
      return Object.values(item).some(function (v) {
        return v !== "";
      });
    });
  }

  function readValue(form, name) {
    var el = form.querySelector('[name="' + name + '"]');
    return el ? (el.value || "").trim() : "";
  }

  function setStatus(root, text, kind) {
    var status = root.querySelector(".wecoop-cv-ai__status");
    status.textContent = text;
    status.setAttribute("data-status", kind);
  }

  function renderFieldErrors(root, fields) {
    var box = root.querySelector(".wecoop-cv-ai__error");
    var entries = Object.entries(fields || {});

    if (!entries.length) {
      box.hidden = true;
      box.innerHTML = "";
      return;
    }

    box.hidden = false;
    box.innerHTML = "<strong>Correggi questi campi:</strong><ul>" + entries.map(function (entry) {
      return "<li>" + entry[0] + ": " + entry[1] + "</li>";
    }).join("") + "</ul>";
  }

  function buildPayload(root) {
    var form = root.querySelector("form");
    var skills = readValue(form, "skills").split(",").map(function (item) {
      return item.trim();
    }).filter(Boolean);

    return {
      personalInfo: {
        firstName: readValue(form, "personalInfo.firstName"),
        lastName: readValue(form, "personalInfo.lastName"),
        birthDate: readValue(form, "personalInfo.birthDate"),
        nationality: readValue(form, "personalInfo.nationality"),
        phone: readValue(form, "personalInfo.phone"),
        email: readValue(form, "personalInfo.email"),
        address: readValue(form, "personalInfo.address"),
      },
      education: collectRows(root.querySelector('[data-repeater="education"]')),
      experience: collectRows(root.querySelector('[data-repeater="experience"]')),
      languages: collectRows(root.querySelector('[data-repeater="languages"]')),
      skills: skills,
      jobGoal: {
        position: readValue(form, "jobGoal.position"),
        country: readValue(form, "jobGoal.country"),
        availability: readValue(form, "jobGoal.availability"),
        industry: readValue(form, "jobGoal.industry"),
      },
      config: {
        template: readValue(form, "config.template"),
        cvLanguage: readValue(form, "config.cvLanguage"),
        includePhoto: !!form.querySelector('[name="config.includePhoto"]').checked,
      },
    };
  }

  function bindRepeater(root, type) {
    var wrapper = root.querySelector('[data-repeater="' + type + '"]');
    var addButton = root.querySelector('[data-add-row="' + type + '"]');

    addButton.addEventListener("click", function () {
      wrapper.insertAdjacentHTML("beforeend", rowTemplate(type));
    });

    wrapper.addEventListener("click", function (event) {
      if (!event.target.matches("[data-remove-row]")) {
        return;
      }

      var row = event.target.closest("[data-row]");
      if (row) {
        row.remove();
      }
    });

    addButton.click();
  }

  async function submitHandler(event, root) {
    event.preventDefault();

    var result = root.querySelector(".wecoop-cv-ai__result");
    var error = root.querySelector(".wecoop-cv-ai__error");
    result.hidden = true;
    error.hidden = true;
    error.innerHTML = "";

    setStatus(root, "Estamos generando tu CV...", "loading");

    try {
      var payload = buildPayload(root);
      var response = await fetch(wecoopCvAiConfig.restUrl + "/cv/generate", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-WP-Nonce": wecoopCvAiConfig.nonce,
        },
        body: JSON.stringify(payload),
      });

      var data = await response.json();
      if (!response.ok || !data.ok) {
        setStatus(root, "Errore durante la generazione del CV.", "error");
        renderFieldErrors(root, data && data.error ? data.error.fields : {});
        return;
      }

      setStatus(root, "CV generato con successo.", "success");

      var preview = root.querySelector(".wecoop-cv-ai__preview");
      preview.textContent = data.previewText || "CV generato correttamente.";

      var pdfLink = root.querySelector('[data-download="pdf"]');
      var docxLink = root.querySelector('[data-download="docx"]');

      pdfLink.href = data.files && data.files.pdfUrl ? data.files.pdfUrl : "#";
      docxLink.href = data.files && data.files.docxUrl ? data.files.docxUrl : "#";

      result.hidden = false;
    } catch (err) {
      setStatus(root, "Servizio temporaneamente non disponibile.", "error");
      renderFieldErrors(root, {});
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    var root = document.querySelector("[data-wecoop-cv-ai]");
    if (!root || typeof wecoopCvAiConfig === "undefined") {
      return;
    }

    ["experience", "education", "languages"].forEach(function (type) {
      bindRepeater(root, type);
    });

    var form = root.querySelector("form");
    form.addEventListener("submit", function (event) {
      submitHandler(event, root);
    });
  });
})();
