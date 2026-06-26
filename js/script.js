const hamburger = document.getElementById("hamburger");
const navMenu = document.getElementById("navMenu");
const overlay = document.getElementById("menuOverlay");

if (hamburger && navMenu && overlay) {
    hamburger.addEventListener("click", () => {
        hamburger.classList.toggle("active");
        navMenu.classList.toggle("active");
        overlay.classList.toggle("active");
    });

    overlay.addEventListener("click", () => {
        hamburger.classList.remove("active");
        navMenu.classList.remove("active");
        overlay.classList.remove("active");
    });

    document.querySelectorAll("#navMenu a").forEach(link => {
        link.addEventListener("click", () => {
            hamburger.classList.remove("active");
            navMenu.classList.remove("active");
            overlay.classList.remove("active");
        });
    });
}

const reveals = document.querySelectorAll('.reveal, .reveal-left, .reveal-right');

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('active');
            observer.unobserve(entry.target);
        }
    });
}, {
    threshold: 0.15
});

reveals.forEach(reveal => {
    observer.observe(reveal);
});

// ANTI-SPAM - TEMPO DE PREENCHIMENTO
document.querySelectorAll("form").forEach(form => {
    const startedInput = form.querySelector('input[name="form_started_at"]');

    if (startedInput) {
        startedInput.value = Date.now();
    }

    form.addEventListener("submit", () => {
        const submitButton = form.querySelector('button[type="submit"]');

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = "Enviando...";
        }
    });
});

const formStatusMessages = {
    success: {
        title: "Mensagem enviada",
        text: "Recebemos sua mensagem com sucesso. Nossa equipe entrara em contato em breve.",
        type: "success"
    },
    invalid: {
        title: "Revise os dados",
        text: "Algumas informacoes parecem incompletas. Confira o formulario e tente novamente.",
        type: "error"
    },
    error: {
        title: "Nao foi possivel enviar",
        text: "O envio nao foi concluido agora. Tente novamente em instantes ou fale conosco pelo WhatsApp.",
        type: "error"
    },
    file_error: {
        title: "Arquivo nao enviado",
        text: "Nao conseguimos receber o arquivo anexado. Selecione o curriculo novamente e tente enviar.",
        type: "error"
    },
    file_size: {
        title: "Arquivo muito grande",
        text: "O curriculo deve ter no maximo 5MB.",
        type: "error"
    },
    file_type: {
        title: "Formato invalido",
        text: "Envie o curriculo em PDF, DOC ou DOCX.",
        type: "error"
    }
};

function closeFormModal(modal) {
    modal.classList.remove("active");
    document.body.classList.remove("modal-open");
    window.history.replaceState({}, document.title, window.location.pathname + window.location.hash);

    setTimeout(() => {
        modal.remove();
    }, 250);
}

function showFormModal(message) {
    const modal = document.createElement("div");
    modal.className = `form-modal ${message.type === "success" ? "is-success" : "is-error"}`;
    modal.setAttribute("role", "dialog");
    modal.setAttribute("aria-modal", "true");
    modal.setAttribute("aria-labelledby", "formModalTitle");

    modal.innerHTML = `
        <div class="form-modal__backdrop" data-close-modal></div>
        <div class="form-modal__card">
            <div class="form-modal__icon" aria-hidden="true"></div>
            <h2 id="formModalTitle">${message.title}</h2>
            <p>${message.text}</p>
            <button type="button" class="btn-primary form-modal__button" data-close-modal>Entendi</button>
        </div>
    `;

    document.body.appendChild(modal);
    document.body.classList.add("modal-open");

    requestAnimationFrame(() => {
        modal.classList.add("active");
        modal.querySelector(".form-modal__button").focus();
    });

    modal.querySelectorAll("[data-close-modal]").forEach(element => {
        element.addEventListener("click", () => closeFormModal(modal));
    });

    document.addEventListener("keydown", function handleEsc(event) {
        if (event.key === "Escape") {
            closeFormModal(modal);
            document.removeEventListener("keydown", handleEsc);
        }
    });
}

const formStatus = new URLSearchParams(window.location.search).get("form_status");

if (formStatus && formStatusMessages[formStatus]) {
    showFormModal(formStatusMessages[formStatus]);
}
