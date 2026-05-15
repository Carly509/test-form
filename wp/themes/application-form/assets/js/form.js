(function () {
    'use strict';

    const form = document.getElementById('application-form');
    if (!form || typeof applicationFormData === 'undefined') return;

    const fields = {
        firstName: document.getElementById('firstName'),
        lastName: document.getElementById('lastName'),
        email: document.getElementById('email'),
        phoneNumber: document.getElementById('phoneNumber'),
        country: document.getElementById('country'),
        dateOfBirth: document.getElementById('dateOfBirth'),
        agreedToTerms: document.getElementById('agreedToTerms'),
    };
    const requiredFields = ['firstName', 'lastName', 'email', 'agreedToTerms'];
    const touched = {};
    let errors = {};

    function getPayload() {
        return {
            firstName: fields.firstName.value,
            lastName: fields.lastName.value,
            email: fields.email.value,
            phoneNumber: fields.phoneNumber.value,
            country: fields.country.value,
            dateOfBirth: fields.dateOfBirth.value,
            agreedToTerms: fields.agreedToTerms.checked,
        };
    }

    function validate(data) {
        const nextErrors = {};

        if (!data.firstName.trim()) nextErrors.firstName = 'First name is required';
        if (!data.lastName.trim()) nextErrors.lastName = 'Last name is required';
        if (!data.email.trim()) {
            nextErrors.email = 'Email is required';
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
            nextErrors.email = 'Please enter a valid email address';
        }
        if (!data.agreedToTerms) {
            nextErrors.agreedToTerms = 'You must agree to the terms and conditions';
        }

        return nextErrors;
    }

    function setStatus(message, isError) {
        const status = document.getElementById('application-status');
        if (!status) return;

        status.textContent = message || '';
        status.classList.toggle('is-visible', Boolean(message));
        status.classList.toggle('is-error', Boolean(isError));
    }

    function render() {
        requiredFields.forEach(function (fieldName) {
            const errorEl = document.getElementById('error-' + fieldName);
            const fieldEl = fields[fieldName];
            const message = errors[fieldName] && touched[fieldName] ? errors[fieldName] : '';

            if (errorEl) {
                errorEl.textContent = message;
                errorEl.classList.toggle('is-visible', Boolean(message));
            }

            if (fieldEl && fieldEl.type !== 'checkbox') {
                fieldEl.classList.toggle('has-error', Boolean(message));
            }
        });

        fields.dateOfBirth.classList.toggle('has-value', Boolean(fields.dateOfBirth.value));
        fields.dateOfBirth.parentElement.classList.toggle('has-value', Boolean(fields.dateOfBirth.value));
    }

    function handleChange(event) {
        const name = event.target.name;

        setStatus('', false);
        if (touched[name]) {
            errors = validate(getPayload());
            render();
        }

        if (name === 'dateOfBirth' || name === 'agreedToTerms') {
            render();
        }
    }

    function handleBlur(event) {
        touched[event.target.name] = true;
        errors = validate(getPayload());
        render();
    }

    Object.keys(fields).forEach(function (fieldName) {
        const field = fields[fieldName];
        if (!field) return;

        field.addEventListener('change', handleChange);
        field.addEventListener('input', handleChange);
        field.addEventListener('blur', handleBlur);
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        requiredFields.forEach(function (fieldName) {
            touched[fieldName] = true;
        });

        errors = validate(getPayload());
        render();

        if (Object.keys(errors).length > 0) return;

        const submit = form.querySelector('.application-submit');
        setStatus('', false);

        if (submit) {
            submit.disabled = true;
        }

        const formData = new FormData(form);
        formData.append('action', applicationFormData.action);
        formData.append('nonce', applicationFormData.nonce);

        fetch(applicationFormData.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData,
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                });
            })
            .then(function (result) {
                const data = result.data;

                if (result.ok && data.success) {
                    form.reset();
                    errors = {};
                    requiredFields.forEach(function (fieldName) {
                        touched[fieldName] = false;
                    });
                    render();
                    setStatus(data.data.message, false);
                    return;
                }

                if (data.data && data.data.errors) {
                    errors = data.data.errors;
                    requiredFields.forEach(function (fieldName) {
                        touched[fieldName] = true;
                    });
                    render();
                }

                setStatus(data.data && data.data.message ? data.data.message : 'An error occurred. Please try again.', true);
            })
            .catch(function () {
                setStatus('An error occurred. Please try again.', true);
            })
            .finally(function () {
                if (submit) {
                    submit.disabled = false;
                }
            });
    });

    render();
})();
