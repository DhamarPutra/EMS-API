<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>API Docs</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-6">
    <div class="max-w-5xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">📘 API Documentation</h1>
        <div id="api-container" class="space-y-6"></div>
    </div>

    <script>
        const methodColors = {
            GET: 'bg-blue-500',
            POST: 'bg-green-500',
            PUT: 'bg-yellow-500',
            DELETE: 'bg-red-500'
        };

        fetch('/routes.json')
            .then(res => res.json())
            .then(routes => {
                const container = document.getElementById('api-container');

                routes.forEach(route => {
                    const methodColor = methodColors[route.method] || 'bg-gray-500';

                    const card = document.createElement('div');
                    card.className = 'bg-white shadow rounded overflow-hidden';

                    const header = document.createElement('div');
                    header.className =
                        `${methodColor} text-white px-4 py-2 font-semibold flex justify-between items-center`;

                    const title = document.createElement('span');
                    title.innerText = `${route.method} ${route.endpoint}`;
                    header.appendChild(title);

                    // Show JSON button if example exists
                    let jsonBox = null;
                    if (route.example && ['POST', 'PUT'].includes(route.method)) {
                        const toggleBtn = document.createElement('button');
                        toggleBtn.className =
                            'text-sm text-white bg-black bg-opacity-30 px-2 py-1 rounded hover:bg-opacity-50';
                        toggleBtn.innerText = 'Show JSON';

                        toggleBtn.onclick = () => {
                            jsonBox.classList.toggle('hidden');
                            toggleBtn.innerText = jsonBox.classList.contains('hidden') ? 'Show JSON' :
                                'Hide JSON';
                        };

                        header.appendChild(toggleBtn);
                    }

                    const body = document.createElement('div');
                    body.className = 'p-4';

                    const desc = document.createElement('p');
                    desc.className = 'text-gray-700 mb-2';
                    desc.innerText = route.description;
                    body.appendChild(desc);

                    // JSON example box
                    if (route.example && ['POST', 'PUT'].includes(route.method)) {
                        jsonBox = document.createElement('pre');
                        jsonBox.className =
                            'mt-2 p-3 bg-gray-100 text-sm rounded font-mono text-gray-800 overflow-x-auto hidden';
                        jsonBox.innerText = JSON.stringify(route.example, null, 2);
                        body.appendChild(jsonBox);
                    }

                    // Form tester
                    if (route.fields && ['POST', 'PUT'].includes(route.method)) {
                        const form = document.createElement('form');
                        form.className = 'space-y-3 mt-4';
                        form.onsubmit = async e => {
                            e.preventDefault();
                            const formData = new FormData(form);
                            const data = {};
                            formData.forEach((value, key) => data[key] = value);

                            const endpoint = route.endpoint.replace('{id}', data.id || '');

                            try {
                                const res = await fetch(endpoint, {
                                    method: route.method,
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify(data)
                                });
                                const json = await res.json();
                                alert('✅ Success:\n' + JSON.stringify(json, null, 2));
                            } catch (err) {
                                alert('❌ Error:\n' + err);
                            }
                        };

                        for (const [field, type] of Object.entries(route.fields)) {
                            const wrapper = document.createElement('div');
                            const label = document.createElement('label');
                            label.className = 'block text-sm font-medium text-gray-700';
                            label.innerText = field;

                            const input = document.createElement('input');
                            input.name = field;
                            input.type = type === 'number' ? 'number' : 'text';
                            input.className =
                                'mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-200 p-2';

                            wrapper.appendChild(label);
                            wrapper.appendChild(input);
                            form.appendChild(wrapper);
                        }

                        const submit = document.createElement('button');
                        submit.type = 'submit';
                        submit.className = 'bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded';
                        submit.innerText = `Test ${route.method}`;

                        form.appendChild(submit);
                        body.appendChild(form);
                    }

                    card.appendChild(header);
                    card.appendChild(body);
                    container.appendChild(card);
                });
            });
    </script>
</body>

</html>
