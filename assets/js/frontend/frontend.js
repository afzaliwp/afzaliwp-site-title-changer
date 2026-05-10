const $ = jQuery;

class AfzaliWPTitleChanger {

	constructor() {
		this.ajaxUrl = afzaliwp_tc_params.ajax_url;
		this.nonce = afzaliwp_tc_params.nonce;
		this.bindEvents();
	}

	bindEvents() {
		$(document).on('submit', '#afzaliwp-tc-login-form', (e) => this.handleLogin(e));
		$(document).on('submit', '#afzaliwp-tc-title-form', (e) => this.handleTitleUpdate(e));
		$(document).on('click', '#afzaliwp-tc-changelog-btn', () => this.handleChangelogOpen());
	}

	handleLogin(e) {
		e.preventDefault();

		const $form = $(e.target);
		const $btn = $form.find('button[type="submit"]');

		this.setLoading($btn, true);

		$.post(this.ajaxUrl, {
			action: 'afzaliwp_tc_login',
			_ajax_nonce: this.nonce,
			username: $form.find('input[name="username"]').val(),
			password: $form.find('input[name="password"]').val(),
		}, (res) => {
			this.setLoading($btn, false);

			if (res.success && res.data.redirect) {
				window.location.reload();
				return;
			}

			$('#afzaliwp-tc-login-message').html(res.data.html);
		}).fail(() => {
			this.setLoading($btn, false);
		});
	}

	handleTitleUpdate(e) {
		e.preventDefault();

		const $form = $(e.target);
		const $btn = $form.find('button[type="submit"]');

		this.setLoading($btn, true);

		$.post(this.ajaxUrl, {
			action: 'afzaliwp_tc_update_title',
			_ajax_nonce: this.nonce,
			site_title: $form.find('input[name="site_title"]').val(),
		}, (res) => {
			this.setLoading($btn, false);
			$('#afzaliwp-tc-title-message').html(res.data.html);
		}).fail(() => {
			this.setLoading($btn, false);
		});
	}

	handleChangelogOpen() {
		const $modal = document.getElementById('afzaliwp-tc-changelog-modal');
		$modal.showModal();

		$('#afzaliwp-tc-changelog-content').html('<div class="text-center py-4"><span class="loading loading-spinner loading-md"></span></div>');

		$.post(this.ajaxUrl, {
			action: 'afzaliwp_tc_get_changelog',
			_ajax_nonce: this.nonce,
		}, (res) => {
			if (res.success) {
				$('#afzaliwp-tc-changelog-content').html(res.data.html);
			} else {
				$('#afzaliwp-tc-changelog-content').html(res.data.html);
			}
		});
	}

	setLoading($btn, loading) {
		if (loading) {
			$btn.prop('disabled', true);
			$btn.find('.afzaliwp-tc-btn-text').addClass('hidden');
			$btn.find('.afzaliwp-tc-btn-loading').removeClass('hidden');
		} else {
			$btn.prop('disabled', false);
			$btn.find('.afzaliwp-tc-btn-text').removeClass('hidden');
			$btn.find('.afzaliwp-tc-btn-loading').addClass('hidden');
		}
	}
}

jQuery(() => {
	new AfzaliWPTitleChanger();
});
