/**
 * Smart Contact Hub - frontend form behaviour.
 * Submits the form via AJAX and manages loading, error, and success states.
 */
(function () {
	'use strict';


	var config = window.schFrontend || {};
	var i18n = config.i18n || {};
	var WHATSAPP_PREFIX = 'https://wa.me/';


	function setStatus(statusEl, message, type) {
		statusEl.textContent = message || '';
		statusEl.className = 'sch-status' + (type ? ' sch-status--' + type : '');
		statusEl.hidden = !message;
	}


	function setLoading(wrap, submitBtn, label, isLoading) {
		submitBtn.disabled = isLoading;
		submitBtn.classList.toggle('is-loading', isLoading);
		submitBtn.querySelector('.sch-submit-text').textContent = isLoading ? i18n.sending : label;
		wrap.setAttribute('aria-busy', isLoading ? 'true' : 'false');
	}


	function showSuccess(refs, data) {
		refs.successText.textContent = data.message || '';


		var hasWhatsApp = typeof data.whatsapp_url === 'string' && data.whatsapp_url.indexOf(WHATSAPP_PREFIX) === 0;
		refs.whatsappLink.hidden = !hasWhatsApp;
		if (hasWhatsApp) {
			refs.whatsappLink.href = data.whatsapp_url;
		}


		refs.formPanel.hidden = true;
		refs.success.hidden = false;
		refs.success.focus();
	}


	function resetForm(refs) {
		refs.form.reset();
		refs.success.hidden = true;
		refs.formPanel.hidden = false;
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


		var buttonLabel = refs.submitBtn.querySelector('.sch-submit-text').textContent;


		refs.form.addEventListener('submit', function (event) {
			event.preventDefault();
			setStatus(refs.status, '', '');


			if (!refs.form.checkValidity()) {
				refs.form.reportValidity();
				return;
			}