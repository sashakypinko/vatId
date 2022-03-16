const form = document.getElementById('form');
const submitBtn = document.getElementById('submitBtn');
const loader = document.getElementById('loader');
const errorContainer = document.getElementById('errorContainer');

const REQUEST_URL = 'http://localhost/index.php'

form.addEventListener('submit', e => {
    e.preventDefault();
    toggleLoading();

    makeRequest(objectifyForm(e.target));
})

function makeRequest(data) {
    const url = new URL(REQUEST_URL);
    url.search = new URLSearchParams(data);

    setError(false)

    fetch(url)
        .then(response => response.json())
        .then((res) => {
            if (res.error) {
                setError(res.error)
            }

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

function setError(error) {
    if(error) {
        errorContainer.innerText = error;
        errorContainer.style.display = 'block';
    } else {
        errorContainer.style.display = 'none';
    }
}
