window.addEventListener('DOMContentLoaded', e => {
	(document.querySelectorAll('.notification .delete') || []).forEach(
		$delete => {
			$notification = $delete.parentNode;

			$delete.addEventListener('click', () => {
				$notification.parentNode.removeChild($notification);
			});
		}
	);

	document.addEventListener('click', e => {
		if (e.target && e.target.matches('.notification .delete')) {
			const $notification = e.target.parentNode;

			$notification.parentNode.removeChild($notification);
		}
	});

	const config = Joomla.getOptions('config');

	const parent = '.view-lighthouses';
	const $fixBtn = document.querySelector(`${parent} .lighthouse-fix`);

	if (config.btnStatus === 'disabled') {
		$fixBtn.setAttribute('disabled', true);
	} else {
		$fixBtn.removeAttribute('disabled');
	}

	const $wrapper = document.querySelector(
		`${parent} .lighthouse-container .lighthouse-wrapper`
	);

	let timeout = null;

	$fixBtn.addEventListener('click', async e => {
		e.preventDefault();

		$wrapper.innerHTML = '<span class="lighthouse-msg">Fixing...</span>';

		if (timeout) clearTimeout(timeout);

		timeout = setTimeout(async () => {
			const url = `${config.base}?option=${config.component}&view=lighthouses&task=lighthouses.fix`;

			const resp = await fetch(url);
			const data = await resp.json();

			if (data) {
				if (data.data.errors.length > 0) {
					$wrapper.innerHTML = `
						<div class='notification is-danger'>
							<button class='delete'></button>
							Some problems could not be fixed automatically. Click the <strong>Fix</strong> button again and if it doesn't solve the problem then you have to fix them manually or contact with the service providers.
						</div>
					`;
				} else {
					$wrapper.innerHTML = '';
				}

				if (data.data.html.length > 0) {
					$wrapper.innerHTML += data.data.html;
				} else {
					$wrapper.innerHTML =
						'<span class="lighthouse-msg">Your Database is perfect, Nothing to fix</span>';
				}

				if (data.data.errors.length === 0) {
					$fixBtn.setAttribute('disabled', true);
				} else {
					$fixBtn.removeAttribute('disabled');
				}
			} else {
				$wrapper.innerHTML =
					'<span class="lighthouse-msg">Your Database is perfect, Nothing to fix</span>';
			}
		}, 1000);
	});
});
