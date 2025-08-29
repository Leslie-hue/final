document.addEventListener('DOMContentLoaded', () => {
    // Navbar scroll effect
    const navbar = document.getElementById('navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            const scrolled = window.scrollY > 50;
            navbar.style.background = scrolled ? 'rgba(255, 255, 255, 0.98)' : 'rgba(255, 255, 255, 0.95)';
            navbar.style.boxShadow = scrolled ? '0 4px 20px rgba(0, 0, 0, 0.1)' : '0 2px 20px rgba(0, 0, 0, 0.08)';
        });
    }

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', e => {
            e.preventDefault();
            const targetId = anchor.getAttribute('href').substring(1);
            const target = document.getElementById(targetId);
            if (target) {
                window.scrollTo({
                    top: target.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Active navigation highlighting
    window.addEventListener('scroll', () => {
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-link:not(.btn)');
        let current = '';

        sections.forEach(section => {
            const sectionTop = section.offsetTop - 100;
            if (window.scrollY >= sectionTop && window.scrollY < sectionTop + section.clientHeight) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    });

    // Mobile menu toggle
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenu = document.getElementById('mobileMenu');
    if (mobileToggle && mobileMenu) {
        mobileToggle.addEventListener('click', () => {
            const isOpen = mobileMenu.style.display === 'block';
            mobileMenu.style.display = isOpen ? 'none' : 'block';
            mobileToggle.querySelector('i').className = isOpen ? 'fas fa-bars' : 'fas fa-times';
            mobileToggle.setAttribute('aria-expanded', !isOpen);
        });

        document.querySelectorAll('.mobile-nav a').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.style.display = 'none';
                mobileToggle.querySelector('i').className = 'fas fa-bars';
                mobileToggle.setAttribute('aria-expanded', 'false');
            });
        });

        document.addEventListener('click', e => {
            if (!mobileMenu.contains(e.target) && !mobileToggle.contains(e.target)) {
                mobileMenu.style.display = 'none';
                mobileToggle.querySelector('i').className = 'fas fa-bars';
                mobileToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Team carousel
    const teamGrid = document.querySelector('.team-grid');
    const prevBtn = document.querySelector('.team-nav-prev');
    const nextBtn = document.querySelector('.team-nav-next');
    if (teamGrid && prevBtn && nextBtn) {
        const cardWidth = () => {
            const card = teamGrid.querySelector('.team-card');
            return card ? card.offsetWidth + parseInt(getComputedStyle(card).marginRight) : 320;
        };

        const updateButtonState = () => {
            const scrollLeft = teamGrid.scrollLeft;
            const maxScroll = teamGrid.scrollWidth - teamGrid.clientWidth;
            prevBtn.disabled = scrollLeft <= 0;
            nextBtn.disabled = scrollLeft >= maxScroll - 1;
        };

        prevBtn.addEventListener('click', () => {
            teamGrid.scrollBy({ left: -cardWidth(), behavior: 'smooth' });
        });

        nextBtn.addEventListener('click', () => {
            teamGrid.scrollBy({ left: cardWidth(), behavior: 'smooth' });
        });

        teamGrid.addEventListener('scroll', updateButtonState);
        window.addEventListener('resize', updateButtonState);
        updateButtonState();
    }

    // Scroll to top
    const scrollToTopBtn = document.getElementById('scrollToTop');
    if (scrollToTopBtn) {
        window.addEventListener('scroll', () => {
            scrollToTopBtn.classList.toggle('show', window.scrollY > 300);
        });
        scrollToTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Lazy load images
    const images = document.querySelectorAll('img[loading="lazy"]');
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.src; // Pas de dataset.src dans home.php
                    observer.unobserve(img);
                }
            });
        }, { rootMargin: '0px 0px 200px 0px' });

        images.forEach(img => observer.observe(img));
    }
});

class FileUploadManager {
    constructor() {
        this.files = [];
        this.maxFileSize = 10 * 1024 * 1024; // 10MB, aligné avec config.php
        this.maxFiles = 5;
        this.allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
        this.stripe = null;
        this.elements = null;
        this.init();
    }

    init() {
        const fileInput = document.getElementById('fileInput');
        const uploadSection = document.getElementById('fileUploadSection');
        const form = document.getElementById('contactForm');

        if (fileInput && uploadSection) {
            uploadSection.addEventListener('dragover', e => this.handleDragOver(e));
            uploadSection.addEventListener('dragleave', e => this.handleDragLeave(e));
            uploadSection.addEventListener('drop', e => this.handleDrop(e));
            fileInput.addEventListener('change', e => this.handleFileSelect(e));
        }

        if (form) {
            form.addEventListener('submit', e => this.handleFormSubmit(e));
            form.addEventListener('input', e => this.handleInputValidation(e));
        }

        this.initializeAppointmentToggle();
        this.initializePaymentOptions();
        this.initializeStripe();
    }

    initializeStripe() {
        const stripePublicKey = 'pk_test_your_stripe_public_key_here'; // Remplacer par la vraie clé publique Stripe
        const stripeSection = document.getElementById('stripeSection');
        if (stripePublicKey && stripeSection && typeof Stripe !== 'undefined') {
            this.stripe = Stripe(stripePublicKey);
            this.elements = this.stripe.elements();
            const paymentElement = this.elements.create('payment', {
                layout: 'tabs',
                fields: {
                    billingDetails: {
                        name: 'auto',
                        email: 'auto'
                    }
                }
            });
            paymentElement.mount('#stripe-payment-element');
        } else {
            console.warn('Stripe non initialisé : clé publique manquante ou élément Stripe absent');
        }
    }

    handleDragOver(e) {
        e.preventDefault();
        e.stopPropagation();
        document.getElementById('fileUploadSection').classList.add('drag-over');
    }

    handleDragLeave(e) {
        e.preventDefault();
        e.stopPropagation();
        if (!e.currentTarget.contains(e.relatedTarget)) {
            document.getElementById('fileUploadSection').classList.remove('drag-over');
        }
    }

    handleDrop(e) {
        e.preventDefault();
        e.stopPropagation();
        document.getElementById('fileUploadSection').classList.remove('drag-over');
        this.addFiles(Array.from(e.dataTransfer.files));
    }

    handleFileSelect(e) {
        this.addFiles(Array.from(e.target.files));
        e.target.value = ''; // Réinitialiser pour permettre de re-sélectionner le même fichier
    }

    handleInputValidation(e) {
        const input = e.target;
        if (input.required && !input.value.trim()) {
            input.classList.add('error');
            input.setAttribute('aria-invalid', 'true');
        } else {
            input.classList.remove('error');
            input.setAttribute('aria-invalid', 'false');
        }
    }

    addFiles(newFiles) {
        for (const file of newFiles) {
            if (this.files.length >= this.maxFiles) {
                this.showMessage(`Maximum ${this.maxFiles} fichiers autorisés`, 'error');
                break;
            }
            if (this.validateFile(file)) {
                const fileId = `file_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
                const fileObj = { id: fileId, file, name: file.name, size: file.size, type: file.type, status: 'ready' };
                this.files.push(fileObj);
                this.renderFilePreview(fileObj);
            }
        }
        this.updateFileInput();
    }

    validateFile(file) {
        if (!this.allowedTypes.includes(file.type)) {
            this.showMessage(`Type de fichier non autorisé : ${file.name}`, 'error');
            return false;
        }
        if (file.size > this.maxFileSize) {
            this.showMessage(`Fichier trop volumineux : ${file.name} (Max: 10MB)`, 'error');
            return false;
        }
        if (this.files.some(f => f.name === file.name && f.size === file.size)) {
            this.showMessage(`Fichier déjà ajouté : ${file.name}`, 'error');
            return false;
        }
        return true;
    }

    renderFilePreview(fileObj) {
        const preview = document.getElementById('filePreview');
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        fileItem.id = fileObj.id;
        fileItem.innerHTML = `
            <div class="file-item-header">
                <i class="${this.getFileIcon(fileObj.type)} file-icon"></i>
                <button type="button" class="file-remove" aria-label="Supprimer ${this.escapeHtml(fileObj.name)}">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
            <div class="file-info">
                <div class="file-name">${this.escapeHtml(fileObj.name)}</div>
                <div class="file-size">${this.formatFileSize(fileObj.size)}</div>
                <div class="file-status success">Prêt à envoyer</div>
            </div>
        `;
        preview.appendChild(fileItem);
        fileItem.querySelector('.file-remove').addEventListener('click', () => this.removeFile(fileObj.id));
    }

    removeFile(fileId) {
        this.files = this.files.filter(f => f.id !== fileId);
        document.getElementById(fileId)?.remove();
        this.updateFileInput();
    }

    updateFileInput() {
        const fileInput = document.getElementById('fileInput');
        if (fileInput) {
            const dataTransfer = new DataTransfer();
            this.files.forEach(fileObj => dataTransfer.items.add(fileObj.file));
            fileInput.files = dataTransfer.files;
        }
    }

    async handleFormSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const submitButton = form.querySelector('#submitBtn');
        const originalText = submitButton.querySelector('#submitText').textContent;
        submitButton.disabled = true;
        submitButton.querySelector('#submitText').textContent = 'Envoi en cours...';
        submitButton.querySelector('i').className = 'fas fa-spinner fa-spin';

        try {
            const isAppointment = document.getElementById('appointmentRequested').value === '1';
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;

            if (isAppointment && !paymentMethod) {
                this.showMessage('Veuillez sélectionner un mode de paiement', 'error');
                throw new Error('Mode de paiement manquant');
            }

            if (isAppointment && paymentMethod === 'online' && this.stripe && this.elements) {
                const { error } = await this.stripe.confirmPayment({
                    elements: this.elements,
                    confirmParams: {
                        return_url: window.location.origin + '/payment-success',
                        payment_method_data: {
                            billing_details: {
                                name: formData.get('name'),
                                email: formData.get('email')
                            }
                        }
                    }
                });

                if (error) {
                    this.showMessage(error.message || 'Erreur lors du paiement', 'error');
                    throw new Error(error.message);
                }
            }

            const response = await fetch('/contact', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            });

            const data = await response.json();
            if (data.success) {
                this.showMessage(data.message || 'Demande envoyée avec succès', 'success');
                form.reset();
                this.files = [];
                document.getElementById('filePreview').innerHTML = '';
                document.getElementById('appointmentToggle').classList.remove('active');
                document.getElementById('appointmentDetails').style.display = 'none';
                document.getElementById('stripeSection').style.display = 'none';
                document.querySelectorAll('.payment-option').forEach(option => option.classList.remove('selected'));
            } else {
                this.showMessage(data.message || 'Erreur lors de l\'envoi du formulaire', 'error');
            }
        } catch (error) {
            console.error('Erreur lors de la soumission du formulaire:', error);
            this.showMessage('Une erreur est survenue, veuillez réessayer', 'error');
        } finally {
            submitButton.disabled = false;
            submitButton.querySelector('#submitText').textContent = originalText;
            submitButton.querySelector('i').className = 'fas fa-paper-plane';
        }
    }

    getFileIcon(type) {
        if (type === 'application/pdf') return 'fas fa-file-pdf';
        if (type.includes('word') || type.includes('document')) return 'fas fa-file-word';
        if (type.includes('image')) return 'fas fa-file-image';
        return 'fas fa-file';
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    showMessage(text, type) {
        const messageElement = document.getElementById('contactMessage');
        if (messageElement) {
            messageElement.innerHTML = `
                <div class="alert alert-${type}">
                    <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : 'check-circle'}" aria-hidden="true"></i> ${this.escapeHtml(text)}
                </div>`;
            messageElement.style.display = 'block';
            messageElement.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            setTimeout(() => {
                messageElement.style.display = 'none';
                messageElement.innerHTML = '';
            }, 7000); // Augmenté à 7s pour plus de lisibilité
        }
    }

    initializeAppointmentToggle() {
        const appointmentToggle = document.getElementById('appointmentToggle');
        const appointmentDetails = document.getElementById('appointmentDetails');
        const appointmentRequested = document.getElementById('appointmentRequested');
        if (appointmentToggle && appointmentDetails && appointmentRequested) {
            appointmentToggle.addEventListener('click', () => {
                const isActive = appointmentToggle.classList.toggle('active');
                appointmentRequested.value = isActive ? '1' : '0';
                appointmentDetails.style.display = isActive ? 'block' : 'none';
                document.getElementById('appointment_date').required = isActive;
                document.getElementById('appointment_time').required = isActive;
                document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
                    radio.required = isActive;
                    if (!isActive) radio.checked = false;
                });
                document.getElementById('stripeSection').style.display = 'none';
                this.updateSubmitButton();
                if (isActive && document.getElementById('appointment_date').value) {
                    document.getElementById('appointment_date').dispatchEvent(new Event('change'));
                }
            });

            const dateInput = document.getElementById('appointment_date');
            const timeSelect = document.getElementById('appointment_time');
            if (dateInput && timeSelect) {
                // Validation de la date minimale (aujourd'hui)
                const today = new Date().toISOString().split('T')[0];
                dateInput.setAttribute('min', today);

                dateInput.addEventListener('change', async () => {
                    const selectedDate = dateInput.value;
                    if (!selectedDate || selectedDate < today) {
                        this.showMessage('Veuillez sélectionner une date future', 'error');
                        timeSelect.disabled = true;
                        timeSelect.innerHTML = '<option value="">Sélectionnez une date valide...</option>';
                        return;
                    }

                    timeSelect.disabled = true;
                    timeSelect.innerHTML = '<option value="">Chargement des créneaux...</option>';

                    // Simulation de créneaux pour le débogage (remplacer par une vraie API)
                    const mockSlots = [
                        { id: '1', time_display: '09:00' },
                        { id: '2', time_display: '10:30' },
                        { id: '3', time_display: '14:00' },
                        { id: '4', time_display: '16:30' }
                    ];

                    try {
                        // Décommentez ceci pour une vraie API
                        /*
                        const response = await fetch(`/api/appointment-slots?date=${encodeURIComponent(selectedDate)}`);
                        const data = await response.json();
                        if (data.success && data.data.slots?.length > 0) {
                            timeSelect.innerHTML = '<option value="">Sélectionnez un créneau...</option>';
                            data.data.slots.forEach(slot => {
                                const option = document.createElement('option');
                                option.value = slot.id;
                                option.textContent = slot.time_display;
                                timeSelect.appendChild(option);
                            });
                            timeSelect.disabled = false;
                        } else {
                            timeSelect.innerHTML = '<option value="">Aucun créneau disponible</option>';
                        }
                        */
                        // Simulation pour le débogage
                        timeSelect.innerHTML = '<option value="">Sélectionnez un créneau...</option>';
                        mockSlots.forEach(slot => {
                            const option = document.createElement('option');
                            option.value = slot.id;
                            option.textContent = slot.time_display;
                            timeSelect.appendChild(option);
                        });
                        timeSelect.disabled = false;
                    } catch (error) {
                        console.error('Erreur lors du chargement des créneaux:', error);
                        timeSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                        this.showMessage('Erreur lors du chargement des créneaux horaires', 'error');
                    }
                });
            }
        }
    }

    initializePaymentOptions() {
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', () => {
                document.querySelectorAll('.payment-option').forEach(option => option.classList.remove('selected'));
                radio.closest('.payment-option').classList.add('selected');
                document.getElementById('stripeSection').style.display = radio.value === 'online' ? 'block' : 'none';
                this.updateSubmitButton();
            });
        });
    }

    updateSubmitButton() {
        const submitBtn = document.getElementById('submitBtn');
        const isAppointment = document.getElementById('appointmentRequested').value === '1';
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
        if (!isAppointment) {
            submitBtn.querySelector('#submitText').textContent = 'Envoyer la demande';
            submitBtn.querySelector('i').className = 'fas fa-paper-plane';
        } else if (paymentMethod) {
            submitBtn.querySelector('#submitText').textContent = paymentMethod.value === 'online' ? 'Payer et Réserver (150€)' : 'Demander un rendez-vous';
            submitBtn.querySelector('i').className = paymentMethod.value === 'online' ? 'fas fa-credit-card' : 'fas fa-calendar-alt';
        } else {
            submitBtn.querySelector('#submitText').textContent = 'Sélectionner un mode de paiement';
            submitBtn.querySelector('i').className = 'fas fa-exclamation-triangle';
            submitBtn.disabled = true;
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.fileUploader = new FileUploadManager();
});