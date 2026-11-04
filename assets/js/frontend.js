/**
 * Smart Contact Hub - accessible, dependency-free AJAX form handling.
 */
(function () {
	'use strict';

	var config = window.schFrontend || {};
	var i18n = config.i18n || {};
	var ajaxUrl = config.ajaxUrl || '';

	function setStatus(element, message, type) {
		if (!element) {
			return;
		}

		element.textContent = message || '';
		element.className = 'sch-status' + (type ? ' sch-status--' + type : '');
		element.hidden = !message;
	}

	function setLoading(wrap, button, label, loading) {
		var labelElement = button.querySelector('.sch-submit-text');

		button.disabled = loading;
		button.classList.toggle('is-loading', loading);
		if (labelElement) {
			labelElement.textContent = loading ? (i18n.sending || 'Sending...') : label;
		}
		wrap.setAttribute('aria-busy', loading ? 'true' : 'false');
	}

	function getWhatsAppUrl(value) {
		if (typeof value !== 'string' || value.indexOf('https://wa.me/') !== 0) {
			return '';
		}

		try {
			var url = new URL(value);
			return 'https:' === url.protocol && 'wa.me' === url.hostname ? url.href : '';
		} catch (error) {
			return '';
		}
	}

	function showSuccess(refs, data) {
		refs.successText.textContent = data.message || '';
		var whatsappUrl = getWhatsAppUrl(data.whatsapp_url);

		refs.whatsappLink.hidden = !whatsappUrl;
		if (whatsappUrl) {
			refs.whatsappLink.href = whatsappUrl;
		} else {
			refs.whatsappLink.removeAttribute('href');
		}

		refs.formPanel.hidden = true;
		refs.success.hidden = false;
		refs.success.focus();
	}

	function resetForm(refs) {
		refs.form.reset();
		setStatus(refs.status, '', '');
		refs.success.hidden = true;
		refs.formPanel.hidden = false;
		refs.whatsappLink.hidden = true;
		refs.whatsappLink.removeAttribute('href');
		refs.form.querySelector('input[name="name"]').focus();
	}

	function initForm(wrap) {
		var refs = {
			wrap: wrap,
			form: wrap.querySelector('.sch-form'),
			formPanel: wrap.querySelector('.sch-form-panel'),
			status: wrap.querySelector('.sch-status'),
			submitBtn: wrap.querySelector('.sch-submit'),
			success: wrap.querySelector('.sch-success'),
			successText: wrap.querySelector('.sch-success-text'),
			whatsappLink: wrap.querySelector('.sch-whatsapp'),
			resetBtn: wrap.querySelector('.sch-reset')
		};

		if (!refs.form || !refs.formPanel || !refs.submitBtn || !refs.success) {
			return;
		}

		var buttonLabel = refs.submitBtn.querySelector('.sch-submit-text');
		buttonLabel = buttonLabel ? buttonLabel.textContent : 'Send Message';

		refs.form.addEventListener('submit', function (event) {
			event.preventDefault();
			setStatus(refs.status, '', '');

			if (!refs.form.checkValidity()) {
				refs.form.reportValidity();
				return;
			}

			if (!ajaxUrl || refs.submitBtn.disabled) {
				return;
			}

			setLoading(wrap, refs.submitBtn, buttonLabel, true);

			window.fetch(ajaxUrl, {
				method: 'POST',
				body: new FormData(refs.form),
				credentials: 'same-origin',
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				}
			})
				.then(function (response) {
					return response.json().then(function (payload) {
						if (!response.ok || !payload || !payload.success) {
							var message = payload && payload.data && payload.data.message;
							throw new Error(message || i18n.error || 'Something went wrong. Please try again.');
						}
						return payload.data || {};
					});
				})
				.then(function (data) {
					showSuccess(refs, data);
				})
				.catch(function (error) {
					var message = error && error.message ? error.message : (i18n.networkError || 'Network error. Please try again.');
					setStatus(refs.status, message, 'error');
				})
				.finally(function () {
					setLoading(wrap, refs.submitBtn, buttonLabel, false);
				});
		});

		if (refs.resetBtn) {
			refs.resetBtn.addEventListener('click', function () {
				resetForm(refs);
			});
		}
	}

	function init() {
		var forms = document.querySelectorAll('[data-sch-form]');
		Array.prototype.forEach.call(forms, initForm);
	}

	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
}());
