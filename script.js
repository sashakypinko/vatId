const form = document.getElementById('form');
const submitBtn = document.getElementById('submitBtn');
const loader = document.getElementById('loader');

const REQUEST_URL = 'http://localhost/index.php'

form.addEventListener('submit', e => {
    e.preventDefault();
    toggleLoading();

    makeRequest(objectifyForm(e.target));
})

function makeRequest(data) {
    const url = new URL(REQUEST_URL)
    url.search = new URLSearchParams(data)

    fetch(url)
        .then((res) => {
            console.log(res)
        })
        .catch((err) => {
            console.error(err);
        })
        .finally(() => {
            toggleLoading(false);
        });
}

function objectifyForm(formData) {
    const form = {};

    for (let i = 0; i < formData.length; i++) {
        const {name, value} = formData[i];

        form[name] = value;
    }

    return form;
}

function toggleLoading(loading = true) {
    submitBtn.disabled = loading;
    loader.style.display = loading ? 'block' : 'none';
}
