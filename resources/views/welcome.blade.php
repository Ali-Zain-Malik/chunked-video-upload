<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Video Upload</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    </head>
    <style>
        *
        {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
    </style>
    <body>
        <h1 class="fs-4 fw-bold text-center mt-2">Video Upload In Chunks</h1>
        <div class="container bg-secondary border rounded d-flex flex-column justify-content-center align-items-center py-2">
            <div class="d-flex w-100 justify-content-around align-items-center mt-2">
                <input type="file" id="video" accept="video/mp4,video/x-mkv,video/avi">
                <button type="button" id="upload-btn" class="btn btn-primary w-25">Upload</button>
            </div>
            <div class="progress w-75 d-flex justify-content-start mt-4 mb-2">
                <div id="progressBar" class="progress-bar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" role="progressbar"></div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <script>
            async function uploadedChunks(file)
            {
                const progressBar = document.getElementById('progressBar');

                const chunkSize = 2 * 1024 * 1024; // 2MB per chunk
                const totalChunks = Math.ceil(file.size / chunkSize);

                for(let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++)
                {
                    const start = chunkIndex * chunkSize;
                    const end = Math.ceil(start + chunkSize, file.size);
                    const chunk = file.slice(start, end);

                    const formData = new FormData();
                    formData.append("video_chunk", chunk);
                    formData.append("chunk_index", chunkIndex);
                    formData.append("total_chunks", totalChunks);
                    formData.append("file_name", file.name);
                    formData.append("_token", "{{ csrf_token() }}")

                    const percentUploaded = ((chunkIndex + 1) / totalChunks) * 100;

                    await fetch("{{ route("upload_video") }}",
                        {
                            method: "POST", 
                            body: formData,
                        })
                        .then(function(response)
                        {
                            if(response.ok)
                            {
                                progressBar.style.width = percentUploaded + "%";
                                progressBar.setAttribute("aria-valuenow", percentUploaded);
                            }
                        })
                        .catch(function(error)
                        {
                            progressBar.setAttribute("aria-valuenow", 0);
                        });
                }
            }

            const vidoe = document.getElementById("video");
            const uploadBtn = document.getElementById("upload-btn");
            uploadBtn.addEventListener("click", function(event)
            {
                event.preventDefault();
                const file = vidoe.files[0];
                if(file)
                {
                    uploadedChunks(file);
                }
                else
                {
                    console.log("Please select a file first");
                }
            });
        </script>
    </body>
</html>
