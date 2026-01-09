(function ($) {
    $.fn.galleryUpload = function (options) {
        options = $.extend({
            language: null,
            showIcons: false,
            acceptFileTypes: null,
            maxNumberOfFiles: null,
            maxFileSize: null,
            minFileSize: null,
            chunkUpload: true,
            maxChunkSize: 1024 * 1024 * 5, // Default is set to 5 MB. Optionally, the size of the chunked file may be increased or decreased under appropriate conditions.
            limitConcurrentUploads: 2, // Limited to 2 simultaneous uploads by default. Can be increased if necessary.
            url: null,
            previewMaxWidth: 80,
            previewMaxHeight: 80
        }, options)

        // This unit set to a new array
        const storedFiles = [];
        // his unit set to an error arrayT
        const storedErrors = [];
        // This unit set to a queued array of files waiting to be uploaded
        let storedQueue = [];
        // This unit set to an uploaded files array
        const uploadedFiles = [];
        // This unit is set to the number of interrupted upload files
        let interruptedFiles = 0;

        // Arrays for indexing invalid inputs
        const storedAcceptFileTypes = []; // data-error_type="1"
        const storedMaxFileSize = []; // data-error_type="2"
        const storedMinFileSize = []; // data-error_type="3"

        // his unit set to an alert array
        let errorMessages = [];

        let responseError = "";

        /////////////////////////////////////////

        // Get a reference to the form
        const form = document.querySelector("form");

        /////////////////////////////////////////

        let languages = {
            en: {
                add_files_Lbl: 'Add files',
                all_start_Lbl: 'Start upload',
                all_cancel_Lbl: 'Cancel all uploads',
                maxNumberOfFiles_Lbl: 'Maximum number of files is exceeded. Up to %d files are allowed to be uploaded.',
                acceptFileTypes_Lbl: 'File type not allowed',
                maxFileSize_Lbl: 'File is too large',
                minFileSize_Lbl: 'File is too small',
                error_uploading_file_Lbl: 'Error uploading file',
                cancel_Lbl: 'Cancel',
                done_Lbl: 'Done',
                no_file_selected_Lbl: 'No file selected',
                no_file_selected_to_delete_Lbl: 'No file selected to delete',
                file_transfer_completed_successfully_Lbl: 'File transfer completed successfully',
                file_transfer_failed_partially_Lbl: 'File transfer failed partially',
                file_transfer_failed_completely_Lbl: 'File transfer failed completely',
                upload_cancelled_Lbl: 'Upload cancelled',
                remove_invalid_inputs_Lbl: 'Please remove invalid inputs!',
                remove_invalid_inputs_acceptFileTypes_Lbl: 'The following file types are allowed: %s',
                remove_invalid_inputs_maxFileSize_Lbl: 'The maximum size allowed for each file is up to %s',
                remove_invalid_inputs_minFileSize_Lbl: 'Each file must exceed the minimum size %s'
            },
            et: {
                add_files_Lbl: 'Lisa faile',
                all_start_Lbl: 'Alusta üleslaadimist',
                all_cancel_Lbl: 'Tühista kõik üleslaadimised',
                maxNumberOfFiles_Lbl: 'Maksimaalne failide arv on ületatud. Üles laadida on lubatud kuni %d faili.',
                acceptFileTypes_Lbl: 'Failitüüp pole lubatud',
                maxFileSize_Lbl: 'Fail on liiga suur',
                minFileSize_Lbl: 'Fail on liiga väike',
                error_uploading_file_Lbl: 'Viga faili üleslaadimisel',
                cancel_Lbl: 'Loobu',
                done_Lbl: 'Valmis',
                no_file_selected_Lbl: 'Fail/failid pole valitud',
                no_file_selected_to_delete_Lbl: 'Kustutamiseks pole valitud ühtegi faili',
                file_transfer_completed_successfully_Lbl: 'Failiedastus on edukalt lõpule viidud',
                file_transfer_failed_partially_Lbl: 'Failiedastus ebaõnnestus osaliselt',
                file_transfer_failed_completely_Lbl: 'Failiedastus ebaõnnestus täielikult',
                upload_cancelled_Lbl: 'Üleslaadimine tühistati',
                remove_invalid_inputs_Lbl: 'Eemaldage kehtetud sisendid!',
                remove_invalid_inputs_acceptFileTypes_Lbl: 'Lubatud on järgmised failitüübid: %s',
                remove_invalid_inputs_maxFileSize_Lbl: 'Iga faili maksimaalne lubatud suurus on kuni %s',
                remove_invalid_inputs_minFileSize_Lbl: 'Iga fail peab ületama minimaalset suurust %s'
            },
            ru: {
                add_files_Lbl: 'Добавить файлы',
                all_start_Lbl: 'Начать загрузку',
                all_cancel_Lbl: 'Отменить все загрузки',
                maxNumberOfFiles_Lbl: 'Превышено максимальное количество файлов. Разрешено загружать до %d файлов.',
                acceptFileTypes_Lbl: 'Недопустимый тип файла',
                maxFileSize_Lbl: 'Файл слишком большой',
                minFileSize_Lbl: 'Файл слишком мал',
                error_uploading_file_Lbl: 'Ошибка загрузки файла',
                cancel_Lbl: 'Отмена',
                done_Lbl: 'Сделанный',
                no_file_selected_Lbl: 'Файл не выбран',
                no_file_selected_to_delete_Lbl: 'Не выбран файл для удаления',
                file_transfer_completed_successfully_Lbl: 'Передача файла успешно завершена',
                file_transfer_failed_partially_Lbl: 'Передача файла не удалась частично',
                file_transfer_failed_completely_Lbl: 'Передача файла полностью не удалась',
                upload_cancelled_Lbl: 'Загрузка отменена',
                remove_invalid_inputs_Lbl: 'Пожалуйста, удалите неверные данные!',
                remove_invalid_inputs_acceptFileTypes_Lbl: 'Разрешены следующие типы файлов: %s',
                remove_invalid_inputs_maxFileSize_Lbl: 'Максимально допустимый размер для каждого файла составляет до %s',
                remove_invalid_inputs_minFileSize_Lbl: 'Каждый файл должен превышать минимальный размер %s'
            }
        };

        // The default language is English (en) unless another language is selected
        options.language = options.language ? options.language : 'en';
        options.language = options.language in languages ? options.language : options.language.split('-')[0]; // fr-CA fallback to fr
        options.language = options.language in languages ? options.language : 'en';

        /////////////////////////////////////////

        // Get a reference to the 5 buttons
        const file_input = document.querySelector(".fileinput-button");
        const input = file_input.querySelector("#files");
        const all_start = document.querySelector(".all-start");
        const all_cancel = document.querySelector(".all-cancel");
        const back = document.querySelector(".back");
        const fileupload_donebar = document.querySelector(".fileupload-donebar");
        const done_button = document.querySelector(".done");


        /////////////////////////////////////////

        // input form handler
        input.addEventListener("change", handleFileSelect);
        // all_start upload handler
        all_start.addEventListener("click", handleForm);
        // all_cancel files delete handler
        all_cancel.addEventListener("click", cancelAllUploads);
        // finished result handler
        done_button.addEventListener("click", doneForm);

        /////////////////////////////////////////

        // Get a reference to the alert/s wrapper
        const alert_wrapper = document.querySelector("#alert-wrapper");
        const alert_multi_wrapper = document.querySelector(".alert-multi-wrapper");

        /////////////////////////////////////////

        // Get a reference to div of the table
        const files = document.querySelector(".files");

        /////////////////////////////////////////

        // Prepare table headers
        const table = document.createElement("table");
        table.className = "table table-striped";
        files.appendChild(table);

        const colgroup = document.createElement("colgroup");
        const col_1 = document.createElement("col");
        col_1.setAttribute("style", "width:10%");
        const col_2 = document.createElement("col");
        col_2.setAttribute("style", "width:55%");
        const col_3 = document.createElement("col");
        col_3.setAttribute("style", "width:20%");
        const col_4 = document.createElement("col");
        col_4.setAttribute("style", "width:15%");
        colgroup.appendChild(col_1);
        colgroup.appendChild(col_2);
        colgroup.appendChild(col_3);
        colgroup.appendChild(col_4);

        const tbody = document.createElement("tbody");
        table.appendChild(colgroup);
        table.appendChild(tbody);

        /////////////////////////////////////////

        function handleFileSelect(e) {
            all_start.classList.remove("disabled");
            all_cancel.classList.remove("disabled");
            back.classList.add("disabled");
            back.setAttribute("disabled", "disabled");
            // Clear the alert/alerts
            alert_wrapper.innerHTML = "";
            alert_multi_wrapper.innerHTML = "";

            const files = e.target.files;
            for (var i = 0; i < files.length; i++) {
                const f = files[i];
                const url = URL.createObjectURL(f);

                f.started = false;
                f.complete = false;
                storedFiles.push(f);
                storedQueue.push(f);

                tbody.innerHTML += "";

                const list = document.createElement("tr");
                tbody.appendChild(list);

                const previewTd = document.createElement("td");
                const previewSpan = document.createElement("span");
                previewSpan.className = "preview";
                previewTd.appendChild(previewSpan);

                /////////////////////////////////////////

                const img = document.createElement("img");
                const ext = f.name.split('.').pop().toLowerCase();

                if (options.showIcons) {
                    previewSpan.innerHTML = getFileIconExtension(ext);
                } else if (f.type.match("image.*")) {

                    img.src = url;
                    img.width = options.previewMaxWidth;
                    img.height = options.previewMaxHeight;
                    previewSpan.appendChild(img);
                } else {
                    previewSpan.innerHTML = getFileIconExtension(ext);
                }

                //////////////////////////////////////////

                const nameTd = document.createElement("td");
                const nameP = document.createElement("p");
                nameP.className = "name";
                const namePText = document.createTextNode(f.name);
                nameP.appendChild(namePText);
                nameTd.appendChild(nameP);

                const progress_bar = document.createElement("div");
                progress_bar.setAttribute("data-name", cleanFileName(f.name));
                progress_bar.className = "progress-bar hidden";

                const progress = document.createElement("div");
                progress.setAttribute("data-name", cleanFileName(f.name));
                progress.className = "progress";
                progress_bar.appendChild(progress);
                nameTd.appendChild(progress_bar);

                const text_error = document.createElement("strong");
                text_error.setAttribute("data-name", cleanFileName(f.name));
                text_error.className = "error text-error";
                text_error.innerText = validate(f) ? validate(f) : "";
                nameTd.appendChild(text_error);

                const sizeTd = document.createElement("td");
                const sizeSpan = document.createElement("span");
                sizeSpan.className = "size";
                const sizeText = document.createTextNode(readableBytes(f.size));
                sizeSpan.appendChild(sizeText);
                sizeTd.appendChild(sizeSpan);

                const percent_bar = document.createElement("div");
                percent_bar.setAttribute("data-name", cleanFileName(f.name));
                percent_bar.className = "percent-bar hidden";
                const percent = document.createElement("span");
                percent.setAttribute("data-name", cleanFileName(f.name));
                percent.className = "percent";
                percent_bar.appendChild(percent);
                sizeTd.appendChild(percent_bar);

                const btnTd = document.createElement("td");
                const cancel = document.createElement("button");
                cancel.className = "btn btn-warning cancel";
                cancel.type = "reset";
                cancel.setAttribute("data-name", cleanFileName(f.name));

                if (validate(f)) {
                    cancel.setAttribute("data-error", validate(f));
                }

                if (typeError(f)) {
                    cancel.setAttribute("data-type_error", typeError(f));
                }

                cancel.innerText = languages[options.language].cancel_Lbl;
                btnTd.appendChild(cancel);

                const readySpan = document.createElement("span");
                readySpan.setAttribute("data-name", cleanFileName(f.name));
                readySpan.className = "ready hidden";
                readySpan.innerHTML = '<svg viewBox="0 0 56 56" class="svg-file svg-check files-svg"><path d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z"></path></svg>';
                btnTd.appendChild(readySpan);

                const errorSpan = document.createElement("span");
                errorSpan.setAttribute("data-name", cleanFileName(f.name));
                errorSpan.className = "error error-bubble hidden";
                errorSpan.innerHTML = '<svg viewBox="0 0 56 56" class="svg-file svg-error files-svg"><path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"></path></svg>';
                btnTd.appendChild(errorSpan);

                // These references add 4 columns to the table
                list.appendChild(previewTd);
                list.appendChild(nameTd);
                list.appendChild(sizeTd);
                list.appendChild(btnTd);
            }

            const children = document.querySelectorAll(".cancel");
            for (let i = 0, len = children.length; i < len; i++) {
                children[i].addEventListener("click", removeFile, true);
            }
            errorsExistenceControl();

            console.log(storedFiles);
        }

        //////////////////////////////////////////

        function handleForm(e) {
            e.preventDefault();

            // Clear the input
            input.value = null;
            // Clear the alert/alerts
            alert_wrapper.innerHTML = "";
            alert_multi_wrapper.innerHTML = "";

            // Clear or reset the inputs
            if (storedFiles.length > 0 || (storedFiles.length > 0 && storedErrors.length > 0)) {
                file_input.classList.add("disabled");
                all_start.classList.add("disabled");
                all_cancel.classList.add("disabled");
                back.classList.add("disabled");
                back.setAttribute("disabled", "disabled");

                const children = document.querySelectorAll(".cancel");
                for (let i = 0, len = children.length; i < len; i++) {
                    children[i].setAttribute('disabled', 'disabled');
                }
            }

            // Reject if the file input is empty & throw alert
            if (storedFiles.length === 0 || (storedFiles.length === 0 && storedErrors.length === 0)) {
                show_alert(languages[options.language].no_file_selected_Lbl, "warning");
                file_input.classList.remove("disabled");
                input.removeAttribute('disabled');
                return;
            }
            checkQueue();
        }

        //////////////////////////////////////////

        function checkQueue() {
            let transfering = 0;
            let newQueue = [];

            //remove lingering items from storedQueue
            for (const i in storedQueue) {
                if (storedQueue.hasOwnProperty(i) && !storedQueue[i].complete && !storedQueue[i].error) {
                    newQueue.push(storedQueue[i]);
                }
            }
            storedQueue = newQueue;

            for (let i in storedQueue) {
                if (storedQueue.hasOwnProperty(i)) {
                    if (storedQueue.hasOwnProperty(i) && !storedQueue[i].started) {
                        uploadFile(storedQueue[i]);
                    }
                    transfering++;

                    if (transfering >= options.limitConcurrentUploads) {
                        return;
                    } else {
                        i++;
                    }
                }
            }
        }

        //////////////////////////////////////////

        function uploadFile(f) {
            if (options.chunkUpload) {
                processFilesChunk(f)
            } else {
                const xhr = new XMLHttpRequest();
                let data = new FormData();

                f.started = true;
                data.append("files", f);
                data.append("chunkEnabled", "false");

                updateProgress(xhr, cleanFileName(f.name));
                uploadFailed(xhr, cleanFileName(f.name));
                uploadComplete(xhr, cleanFileName(f.name));
                if (options.url) {
                    xhr.open('POST', options.url, true);
                } else {
                    xhr.open('POST', form.getAttribute("action"), true);
                }
                xhr.send(data);
            }
        }

        //////////////////////////////////////////

        function processFilesChunk(f) {
            f.started = true;
            const chunkSize = options.maxChunkSize;
            const numberOfChunks = Math.ceil(f.size / chunkSize);
            let chunkCounter = 0;
            const start = 0;

            createChunk(start);

            function createChunk(start) {
                chunkCounter++;

                const chunkEnd = Math.min(start + chunkSize, f.size);
                const chunk = f.slice(start, chunkEnd);

                const data = new FormData();
                data.append("chunkEnabled", "true");
                data.append('files', chunk, f.name);
                data.append('chunk', chunkCounter);
                data.append('index', start);
                data.append('count', numberOfChunks);

                //created the chunk, now upload it
                uploadChunk(data, start, chunkEnd);
            }

            function uploadChunk(data, start, chunkEnd) {
                const xhr = new XMLHttpRequest();

                if (options.url) {
                    xhr.open('POST', options.url, true);
                } else {
                    xhr.open('POST', form.getAttribute("action"));
                }
                xhr.upload.addEventListener("progress", updateProgress);

                const blobEnd = chunkEnd - 1;
                xhr.send(data);

                let progress_bar = document.querySelector('.progress-bar[data-name="' + cleanFileName(f.name) + '"]');
                let progress = document.querySelector('.progress[data-name="' + cleanFileName(f.name) + '"]');
                let percent_bar = document.querySelector('.percent-bar[data-name="' + cleanFileName(f.name) + '"]');
                let percent = document.querySelector('.percent[data-name="' + cleanFileName(f.name) + '"]');
                let cancel = document.querySelector('.cancel[data-name="' + cleanFileName(f.name) + '"]');
                let ready = document.querySelector('.ready[data-name="' + cleanFileName(f.name) + '"]');
                let error_bubble = document.querySelector('.error-bubble[data-name="' + cleanFileName(f.name) + '"]');
                let text_error = document.querySelector('.text-error[data-name="' + cleanFileName(f.name) + '"]');

                progress_bar.classList.remove("hidden");
                percent_bar.classList.remove("hidden");

                function updateProgress(e) {
                    if (e.lengthComputable) {
                        const totalPercentComplete = Math.ceil((chunkCounter / numberOfChunks) * 100);

                        // Update the progress text and progress bar
                        progress.setAttribute("style", `width: ${Math.floor(totalPercentComplete) + "%"}`);

                        if (totalPercentComplete > 0) {
                            percent.innerText = `${Math.floor(totalPercentComplete) + "%"}`;
                        }

                        if (this.response) {
                            const response = JSON.parse(this.response);

                            response.started = true;
                            response.complete = false;

                            let output = {
                                "filename": response.filename,
                                "error": response.error,
                            }

                            if (response.error) {
                                text_error.innerText = response.error;
                                error_bubble.classList.remove("hidden");

                                progress_bar.classList.add("hidden");
                                percent_bar.classList.add("hidden");
                                cancel.classList.add("hidden");
                                ready.classList.add("hidden");
                            }
                        } else if (chunkCounter === numberOfChunks) {
                            progress_bar.classList.add("hidden");
                            percent_bar.classList.add("hidden");
                            cancel.classList.add("hidden");
                            ready.classList.remove("hidden");

                            all_start.classList.add("disabled");
                            all_cancel.classList.add("disabled");

                            back.classList.add("disabled");
                            back.setAttribute("disabled", "disabled");

                            done_button.classList.remove("hidden");
                        }
                    }
                }

                xhr.onload = function (e) {
                    if (this.response) {
                        const response = JSON.parse(this.response);

                        response.started = true;
                        response.complete = true;

                        let output = {
                            "filename": response.filename,
                            "error": response.error,
                        }

                        if (response.error) {
                            text_error.innerText = response.error;
                            interruptedFiles++;
                        }

                        uploadedFiles.push(output);
                        storedQueue.shift(output);
                    }

                    //We start one chunk in, as we have uploaded the first one.
                    //Next chunk starts at + chunkSize from start
                    start += chunkSize;
                    //If start is smaller than file size - we have more to still upload
                    if (start < f.size) {
                        //create the new chunk
                        createChunk(start);
                    } else {
                        f.complete = true;
                        checkQueue();
                    }
                    uploadResponses(storedFiles, uploadedFiles, interruptedFiles);
                }

                xhr.onerror = function (e) {
                    text_error.innerText = options.error_uploading_file_Lbl ? options.error_uploading_file_Lbl : '';
                    progress_bar.classList.add("hidden");
                    percent_bar.classList.add("hidden");
                    percent.classList.add("hidden");
                    cancel.classList.add("hidden");
                    error_bubble.classList.remove("hidden");
                    interruptedFiles++;
                }
            }
        }

        //////////////////////////////////////////

        function updateProgress(xhr, i) {
            let progress_bar = document.querySelector('.progress-bar[data-name="' + i + '"]');
            let progress = document.querySelector('.progress[data-name="' + i + '"]');
            let percent_bar = document.querySelector('.percent-bar[data-name="' + i + '"]');
            let percent = document.querySelector('.percent[data-name="' + i + '"]');
            let cancel = document.querySelector('.cancel[data-name="' + i + '"]');
            let ready = document.querySelector('.ready[data-name="' + i + '"]');
            let error_bubble = document.querySelector('.error-bubble[data-name="' + i + '"]');
            let text_error = document.querySelector('.text-error[data-name="' + i + '"]');

            progress_bar.classList.remove("hidden");
            percent_bar.classList.remove("hidden");

            xhr.upload.addEventListener("progress", function (e) {
                if (e.lengthComputable) {
                    // Calculate percent uploaded
                    let percent_complete = (e.loaded / e.total) * 100;

                    // Update the progress text and progress bar
                    progress.setAttribute("style", `width: ${Math.floor(percent_complete) + "%"}`);
                    percent.innerText = `${Math.floor(percent_complete) + "%"}`;

                    if (e.loaded === e.total) {
                        progress_bar.classList.add("hidden");
                        percent_bar.classList.add("hidden");
                        cancel.classList.add("hidden");
                        ready.classList.remove("hidden");

                        all_start.classList.add("disabled");
                        all_start.setAttribute('disabled', 'disabled');
                        all_cancel.classList.add("disabled");
                        all_cancel.setAttribute('disabled', 'disabled');

                        back.classList.add("disabled");
                        back.setAttribute("disabled", "disabled");
                    }
                }
            })

            xhr.onload = function () {
                if (xhr.status >= 200 && xhr.status < 300) {
                    const response = JSON.parse(xhr.responseText);

                    if (response.error) {
                        text_error.innerText = response.error;
                        ready.classList.add("hidden");
                        error_bubble.classList.remove("hidden");

                        progress_bar.classList.add("hidden");
                        percent_bar.classList.add("hidden");
                        cancel.classList.add("hidden");
                        interruptedFiles++;
                    }
                } else {
                    text_error.innerText = response.error;
                    error_bubble.classList.remove("hidden");
                }
            }

            uploadResponses(storedFiles, uploadedFiles, interruptedFiles);

            xhr.onerror = function () {
                text_error.innerText = "An error occurred during the upload.";
                error_bubble.classList.remove("hidden");
            }
        }

        // xhr load handler (transfer complete)
        function uploadComplete(xhr, i) {
            xhr.upload.addEventListener("load", function () {
                xhr.onload = function () { // Määra onload otse siia
                    if (xhr.status === 200) { // Vaid siis, kui üleslaadimine on edukas
                        if (xhr.response) {
                            const response = JSON.parse(xhr.response);
                            response.started = true;
                            response.complete = true;

                            let text_error = document.querySelector('.text-error[data-name="' + cleanFileName(response.filename) + '"]');
                            let ready = document.querySelector('.ready[data-name="' + cleanFileName(response.filename) + '"]');
                            let error_bubble = document.querySelector('.error-bubble[data-name="' + cleanFileName(response.filename) + '"]');

                            if (response.hasOwnProperty("filename")) {
                                let output = {
                                    "filename": response.filename,
                                    "error": response.error,
                                };

                                if (response.error) {
                                    text_error.innerText = response.error;
                                }

                                if (response.error) {
                                    text_error.innerText = response.error;
                                    ready.classList.add("hidden");
                                    error_bubble.classList.remove("hidden");
                                    interruptedFiles++;
                                }

                                uploadedFiles.push(output);
                                storedQueue.shift(output);
                                i.complete = true;
                                checkQueue();
                            }

                            uploadResponses(storedFiles, uploadedFiles, interruptedFiles);
                        }
                    } else {
                        console.error('Upload failed with status:', xhr.status);
                    }
                }
            })
        }

        // xhr error handler
        function uploadFailed(xhr, i) {
            let progress_bar = document.querySelector('.progress-bar[data-name="' + i + '"]');
            let percent_bar = document.querySelector('.percent-bar[data-name="' + i + '"]');
            let percent = document.querySelector('.percent[data-name="' + i + '"]');
            let cancel = document.querySelector('.cancel[data-name="' + i + '"]');
            let error_bubble = document.querySelector('.error-bubble[data-name="' + i + '"]');
            let text_error = document.querySelector('.text-error[data-name="' + i + '"]');

            xhr.upload.addEventListener("error", function () {
                text_error.innerText = languages[options.language].error_uploading_file_Lbl ? languages[options.language].error_uploading_file_Lbl : '';
                progress_bar.classList.add("hidden");
                percent_bar.classList.add("hidden");
                percent.classList.add("hidden");
                cancel.classList.add("hidden");
                error_bubble.classList.remove("hidden");
                interruptedFiles++;
            });

             uploadResponses(storedFiles, uploadedFiles, interruptedFiles);
        }

        //////////////////////////////////////////

        function uploadResponses(s, u, i) {
            if (s.length === u.length) {
                if ((s.length === u.length) && i === 0) {
                    show_alert(languages[options.language].file_transfer_completed_successfully_Lbl, "success");
                    fileupload_donebar.classList.remove("hidden");
                } else if (u.length === u.length && u.length === i) {
                    show_alert(languages[options.language].file_transfer_failed_completely_Lbl, "danger");
                    fileupload_donebar.classList.remove("hidden");
                } else if ((s.length === u.length) && u.length !== i) {
                    show_alert(languages[options.language].file_transfer_failed_partially_Lbl, "warning");
                    fileupload_donebar.classList.remove("hidden");
                }
            }
        }

        //////////////////////////////////////////

        function validate(e) {
            fileExt = e.name.split('.').pop().toLowerCase();
            if (options.acceptFileTypes &&
                (options.acceptFileTypes.includes(fileExt)) === false) {
                return languages[options.language].acceptFileTypes_Lbl;
            }

            if (options.maxFileSize && options.maxFileSize < e.size) {
                return languages[options.language].maxFileSize_Lbl;
            }

            if (options.minFileSize && options.minFileSize > e.size) {
                return languages[options.language].minFileSize_Lbl;
            }

            return '';
        }

        function typeError(e) {
            fileExt = e.name.split('.').pop().toLowerCase();

            if (options.acceptFileTypes &&
                (options.acceptFileTypes.includes(fileExt)) === false) {
                return 1;
            }

            if (options.maxFileSize && options.maxFileSize < e.size) {
                return 2;
            }

            if (options.minFileSize && options.minFileSize > e.size) {
                return 3;
            }
        }

        function errorsExistenceControl() {

            const Errors = document.querySelectorAll(".text-error");
            for (let i = 0, len = Errors.length; i < len; i++) {
                if (Errors[i].firstChild) {
                    storedErrors.push(Errors[i].firstChild);
                }
            }

            const Error_1 = document.querySelectorAll('.cancel[data-type_error="1"]');
            for (let i = 0, len = Error_1.length; i < len; i++) {
                if (Error_1[i].dataset["type_error"] === "1") {
                    storedAcceptFileTypes.push(Error_1[i].dataset["type_error"]);
                }
            }

            const Error_2 = document.querySelectorAll('.cancel[data-type_error="2"]');
            for (let i = 0, len = Error_2.length; i < len; i++) {
                if (Error_2[i].dataset["type_error"] === "2") {
                    storedMaxFileSize.push(Error_2[i].dataset["type_error"]);
                }
            }

            const Error_3 = document.querySelectorAll('.cancel[data-type_error="3"]');
            for (let i = 0, len = Error_3.length; i < len; i++) {
                if (Error_3[i].dataset["type_error"] === "3") {
                    storedMinFileSize.push(Error_3[i].dataset["type_error"]);
                }
            }

            if (storedErrors.length === 0 && options.maxNumberOfFiles &&
                options.maxNumberOfFiles < storedFiles.length) {
                restrictionInfluencing();
                show_alert(sprintf(languages[options.language].maxNumberOfFiles_Lbl, options.maxNumberOfFiles), "danger");
                alert_multi_wrapper.innerHTML = "";
            } else {
                restrictionRemoving();
            }

            if (options.acceptFileTypes && storedAcceptFileTypes.length > 0) {
                errorMessages.push(sprintf(languages[options.language].remove_invalid_inputs_acceptFileTypes_Lbl, removeSpecialChars(options.acceptFileTypes)));
            }

            if (options.maxFileSize && storedMaxFileSize.length > 0) {
                errorMessages.push(sprintf(languages[options.language].remove_invalid_inputs_maxFileSize_Lbl, readableBytes(options.maxFileSize)));
            }

            if (options.minFileSize && storedMinFileSize.length > 0) {
                errorMessages.push(sprintf(languages[options.language].remove_invalid_inputs_minFileSize_Lbl, readableBytes(options.minFileSize)));
            }

            show_multi_alert(errorMessages);

            if (storedErrors.length > 0) {
                restrictionInfluencing();
                show_alert(sprintf(languages[options.language].remove_invalid_inputs_Lbl, null), "danger");
            } else {
                restrictionRemoving();
                alert_multi_wrapper.innerHTML = "";
            }
        }

        //////////////////////////////////////////

        function cancelAllUploads() {
            // If undo the action back, throw an alert
            if (storedFiles.length > 0 || (storedFiles.length > 0 && storedErrors.length > 0)) {
                show_alert(languages[options.language].upload_cancelled_Lbl, "info");
                while (storedFiles.length > 0) {
                    storedFiles.pop();
                    storedQueue.pop();
                    storedErrors.pop();
                    errorMessages.pop();
                }
                while (storedErrors.length > 0) {
                    storedErrors.pop();
                }
                while (storedAcceptFileTypes.length > 0) {
                    storedAcceptFileTypes.pop();
                }
                while (storedMaxFileSize.length > 0) {
                    storedMaxFileSize.pop();
                }
                while (storedMinFileSize.length > 0) {
                    storedMinFileSize.pop();
                }

                // Clear or reset the inputs and alerts
                tbody.innerHTML = "";
                input.value = null;
                alert_multi_wrapper.innerHTML = "";
                file_input.classList.remove("disabled");
                all_start.classList.add("disabled");
                all_cancel.classList.add("disabled");

                back.classList.remove("disabled");
                back.removeAttribute("disabled");
                return;
            }

            // Let know if the content is empty & throw alert
            if (storedFiles.length === 0 || (storedFiles.length === 0 && storedErrors.length === 0)) {
                show_alert(languages[options.language].no_file_selected_to_delete_Lbl, "info");
                file_input.classList.remove("disabled");
                all_start.classList.add("disabled");
                all_cancel.classList.add("disabled");
            }
        }

        function removeFile() {
            let dataName = this.getAttribute("data-name");
            for (let i = 0; i < storedFiles.length; i++) {
                if (cleanFileName(storedFiles[i].name) === dataName) {
                    storedFiles.splice(i, 1);
                    storedQueue.splice(i, 1);
                    break;
                }
            }

            let errorData = this.getAttribute("data-error");
            if (errorData) {
                for (let i = 0, len = storedErrors[i].length; i < len; i++) {
                    if (storedErrors[i].data === errorData) {
                        storedErrors.splice(i, 1);
                        break;
                    }
                }
            }

            let errorType = this.getAttribute("data-type_error");
            if (errorType) {
                for (let i = 0; i < storedAcceptFileTypes.length; i++) {
                    if (storedAcceptFileTypes[i] === errorType) {
                        storedAcceptFileTypes.splice(i, 1);
                        break;
                    }
                }

                for (let i = 0; i < storedMaxFileSize.length; i++) {
                    if (storedMaxFileSize[i] === errorType) {
                        storedMaxFileSize.splice(i, 1);
                        break;
                    }
                }

                for (let i = 0; i < storedMinFileSize.length; i++) {
                    if (storedMinFileSize[i] === errorType) {
                        storedMinFileSize.splice(i, 1);
                        break;
                    }
                }
            }

            if (storedErrors.length === 0 && options.maxNumberOfFiles &&
                options.maxNumberOfFiles < storedFiles.length) {
                restrictionInfluencing();
                alert_multi_wrapper.innerHTML = "";
                show_alert(sprintf(languages[options.language].maxNumberOfFiles_Lbl, options.maxNumberOfFiles), "danger");
            } else if (storedErrors.length !== 0) {
                restrictionInfluencing();
                show_alert(sprintf(languages[options.language].remove_invalid_inputs_Lbl, null), "danger");
            } else {
                restrictionRemoving();
                // Clear alerts and data
                errorMessages.pop();
                alert_multi_wrapper.innerHTML = "";
            }

            this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode);

            if (storedFiles.length === 0) {
                all_start.classList.add("disabled");
                all_cancel.classList.add("disabled");

                back.classList.remove("disabled");
                back.removeAttribute("disabled");
            }
        }

        //////////////////////////////////////////

        function restrictionInfluencing() {
            file_input.classList.add("disabled");
            all_start.classList.add("disabled");
        }

        function restrictionRemoving() {
            file_input.classList.remove("disabled");
            all_start.classList.remove("disabled");
            alert_wrapper.innerHTML = "";
        }

        //////////////////////////////////////////

        function doneForm() {
            // Clear or reset the inputs and alerts
            file_input.value = null;
            alert_wrapper.innerHTML = "";
            alert_multi_wrapper.innerHTML = "";
            tbody.innerHTML = "";

            file_input.classList.remove("disabled");
            input.removeAttribute('disabled');
            all_start.removeAttribute('disabled');
            all_cancel.removeAttribute('disabled');

            back.classList.remove("disabled");
            back.removeAttribute("disabled");

            fileupload_donebar.classList.add("hidden");

            while (storedFiles.length > 0) {
                storedFiles.pop();
                storedQueue.pop();
                uploadedFiles.pop();
            }
        }

        //////////////////////////////////////////

        // Function to show alert
        // Use to color the alert: success, info, warning, danger
        function show_alert(message, alert) {
            alert_wrapper.innerHTML = `
    <div class="alert alert-${alert} alert-dismissible" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      ${message}
    </div>`
        }

        // Function to show alerts
        function show_multi_alert(messages) {
            let messageHtml = "";
            messageHtml += '<div class="alert alert-warning alert-dismissible" role="alert">' +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
            messageHtml += "<ul>";

            messages.forEach(function (message) {
                messageHtml += "<li>" + message + "</li>";
            });

            messageHtml += "</ul>";
            messageHtml += "</div>";

            alert_multi_wrapper.innerHTML = messageHtml;
        }

        //////////////////////////////////////////

        function cleanFileName(str) {
            if (str) {
                return str = str.replace(/(\W+)/gi, '-');
            }
        }

        function removeSpecialChars(str) {
            if (str) {
                return str.join(', ');
            }
        }

        function readableBytes(bytes) {
            const i = Math.floor(Math.log(bytes) / Math.log(1024)),
                sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            return (bytes / Math.pow(1024, i)).toFixed(2) + ' ' + sizes[i];
        }

        function sprintf() {
            let args = arguments,
                string = args[0],
                i = 1;
            return string.replace(/%((%)|s|d)/g, function (m) {
                // m is the matched format, e.g. %s, %d
                let val = null;
                if (m[2]) {
                    val = m[2];
                } else {
                    val = args[i];
                    // A switch statement so that the formatter can be extended. Default is %s
                    switch (m) {
                        case '%d':
                            val = parseFloat(val);
                            if (isNaN(val)) {
                                val = 0;
                            }
                            break;
                    }
                    i++;
                }
                return val;
            });
        }

        function getFileIconExtension(ext) {
            let icon;
            switch (ext) {
                case 'gif':
                case 'jpg':
                case 'jpeg':
                case 'jpc':
                case 'png':
                case 'bmp':
                    icon = '<svg class="svg-file svg-image files-svg" viewBox="0 0 56 56"><path class="svg-file-bg" d="M36.985,0H7.963C7.155,0,6.5,0.655,6.5,1.926V55c0,0.345,0.655,1,1.463,1h40.074 c0.808,0,1.463-0.655,1.463-1V12.978c0-0.696-0.093-0.92-0.257-1.085L37.607,0.257C37.442,0.093,37.218,0,36.985,0z"></path><polygon class="svg-file-flip" points="37.5,0.151 37.5,12 49.349,12"></polygon><g class="svg-file-icon"> <circle cx="18.931" cy="14.431" r="4.569" style="fill:#f3d55b"></circle> <polygon points="6.5,39 17.5,39 49.5,39 49.5,28 39.5,18.5 29,30 23.517,24.517" style="fill:#88c057"></polygon> </g> <path class="svg-file-text-bg" d="M48.037,56H7.963C7.155,56,6.5,55.345,6.5,54.537V39h43v15.537C49.5,55.345,48.845,56,48.037,56z"></path> <text class="svg-file-ext" x="28" y="51.5">' + ext + '</text> </svg>';
                    break;
                case 'pdf':
                    icon = '<svg class="svg-file svg-pdf files-svg" viewBox="0 0 56 56"> <path class="svg-file-bg" d="M36.985,0H7.963C7.155,0,6.5,0.655,6.5,1.926V55c0,0.345,0.655,1,1.463,1h40.074 c0.808,0,1.463-0.655,1.463-1V12.978c0-0.696-0.093-0.92-0.257-1.085L37.607,0.257C37.442,0.093,37.218,0,36.985,0z"></path><polygon class="svg-file-flip" points="37.5,0.151 37.5,12 49.349,12"></polygon><g class="svg-file-icon"><path d="M19.514,33.324L19.514,33.324c-0.348,0-0.682-0.113-0.967-0.326 c-1.041-0.781-1.181-1.65-1.115-2.242c0.182-1.628,2.195-3.332,5.985-5.068c1.504-3.296,2.935-7.357,3.788-10.75 c-0.998-2.172-1.968-4.99-1.261-6.643c0.248-0.579,0.557-1.023,1.134-1.215c0.228-0.076,0.804-0.172,1.016-0.172 c0.504,0,0.947,0.649,1.261,1.049c0.295,0.376,0.964,1.173-0.373,6.802c1.348,2.784,3.258,5.62,5.088,7.562 c1.311-0.237,2.439-0.358,3.358-0.358c1.566,0,2.515,0.365,2.902,1.117c0.32,0.622,0.189,1.349-0.39,2.16 c-0.557,0.779-1.325,1.191-2.22,1.191c-1.216,0-2.632-0.768-4.211-2.285c-2.837,0.593-6.15,1.651-8.828,2.822 c-0.836,1.774-1.637,3.203-2.383,4.251C21.273,32.654,20.389,33.324,19.514,33.324z M22.176,28.198 c-2.137,1.201-3.008,2.188-3.071,2.744c-0.01,0.092-0.037,0.334,0.431,0.692C19.685,31.587,20.555,31.19,22.176,28.198z M35.813,23.756c0.815,0.627,1.014,0.944,1.547,0.944c0.234,0,0.901-0.01,1.21-0.441c0.149-0.209,0.207-0.343,0.23-0.415 c-0.123-0.065-0.286-0.197-1.175-0.197C37.12,23.648,36.485,23.67,35.813,23.756z M28.343,17.174 c-0.715,2.474-1.659,5.145-2.674,7.564c2.09-0.811,4.362-1.519,6.496-2.02C30.815,21.15,29.466,19.192,28.343,17.174z M27.736,8.712c-0.098,0.033-1.33,1.757,0.096,3.216C28.781,9.813,27.779,8.698,27.736,8.712z"></path></g><path class="svg-file-text-bg" d="M48.037,56H7.963C7.155,56,6.5,55.345,6.5,54.537V39h43v15.537C49.5,55.345,48.845,56,48.037,56z"></path><text class="svg-file-ext" x="28" y="51.5">' + ext + '</text></svg>';
                    break;
                case 'docx':
                case 'doc':
                    icon = '<svg class="svg-file svg-word files-svg" viewBox="0 0 56 56"><path class="svg-file-bg" d="M36.985,0H7.963C7.155,0,6.5,0.655,6.5,1.926V55c0,0.345,0.655,1,1.463,1h40.074 c0.808,0,1.463-0.655,1.463-1V12.978c0-0.696-0.093-0.92-0.257-1.085L37.607,0.257C37.442,0.093,37.218,0,36.985,0z"></path><polygon class="svg-file-flip" points="37.5,0.151 37.5,12 49.349,12"></polygon><g class="svg-file-icon"><path d="M12.5,13h6c0.553,0,1-0.448,1-1s-0.447-1-1-1h-6c-0.553,0-1,0.448-1,1S11.947,13,12.5,13z"></path><path d="M12.5,18h9c0.553,0,1-0.448,1-1s-0.447-1-1-1h-9c-0.553,0-1,0.448-1,1S11.947,18,12.5,18z"></path><path d="M25.5,18c0.26,0,0.52-0.11,0.71-0.29c0.18-0.19,0.29-0.45,0.29-0.71c0-0.26-0.11-0.52-0.29-0.71 c-0.38-0.37-1.04-0.37-1.42,0c-0.181,0.19-0.29,0.44-0.29,0.71s0.109,0.52,0.29,0.71C24.979,17.89,25.24,18,25.5,18z"></path><path d="M29.5,18h8c0.553,0,1-0.448,1-1s-0.447-1-1-1h-8c-0.553,0-1,0.448-1,1S28.947,18,29.5,18z"></path><path d="M11.79,31.29c-0.181,0.19-0.29,0.44-0.29,0.71s0.109,0.52,0.29,0.71 C11.979,32.89,12.229,33,12.5,33c0.27,0,0.52-0.11,0.71-0.29c0.18-0.19,0.29-0.45,0.29-0.71c0-0.26-0.11-0.52-0.29-0.71 C12.84,30.92,12.16,30.92,11.79,31.29z"></path><path d="M24.5,31h-8c-0.553,0-1,0.448-1,1s0.447,1,1,1h8c0.553,0,1-0.448,1-1S25.053,31,24.5,31z"></path><path d="M41.5,18h2c0.553,0,1-0.448,1-1s-0.447-1-1-1h-2c-0.553,0-1,0.448-1,1S40.947,18,41.5,18z"></path><path d="M12.5,23h22c0.553,0,1-0.448,1-1s-0.447-1-1-1h-22c-0.553,0-1,0.448-1,1S11.947,23,12.5,23z"></path><path d="M43.5,21h-6c-0.553,0-1,0.448-1,1s0.447,1,1,1h6c0.553,0,1-0.448,1-1S44.053,21,43.5,21z"></path><path d="M12.5,28h4c0.553,0,1-0.448,1-1s-0.447-1-1-1h-4c-0.553,0-1,0.448-1,1S11.947,28,12.5,28z"></path><path d="M30.5,26h-10c-0.553,0-1,0.448-1,1s0.447,1,1,1h10c0.553,0,1-0.448,1-1S31.053,26,30.5,26z"></path><path d="M43.5,26h-9c-0.553,0-1,0.448-1,1s0.447,1,1,1h9c0.553,0,1-0.448,1-1S44.053,26,43.5,26z"></path></g><path class="svg-file-text-bg" d="M48.037,56H7.963C7.155,56,6.5,55.345,6.5,54.537V39h43v15.537C49.5,55.345,48.845,56,48.037,56z"></path><text class="svg-file-ext" x="28" y="51.5">' + ext + '</text></svg>';
                    break;
                case 'xlsx':
                case 'xls':
                    icon = '<svg viewBox="0 0 56 56" class="svg-file svg-excel files-svg"><path class="svg-file-bg" d="M36.985,0H7.963C7.155,0,6.5,0.655,6.5,1.926V55c0,0.345,0.655,1,1.463,1h40.074 c0.808,0,1.463-0.655,1.463-1V12.978c0-0.696-0.093-0.92-0.257-1.085L37.607,0.257C37.442,0.093,37.218,0,36.985,0z"></path><polygon class="svg-file-flip" points="37.5,0.151 37.5,12 49.349,12"></polygon><g class="svg-file-icon"><path style="fill:#c8bdb8" d="M23.5,16v-4h-12v4v2v2v2v2v2v2v2v4h10h2h21v-4v-2v-2v-2v-2v-2v-4H23.5z M13.5,14h8v2h-8V14z M13.5,18h8v2h-8V18z M13.5,22h8v2h-8V22z M13.5,26h8v2h-8V26z M21.5,32h-8v-2h8V32z M42.5,32h-19v-2h19V32z M42.5,28h-19v-2h19V28 z M42.5,24h-19v-2h19V24z M23.5,20v-2h19v2H23.5z"></path></g><path class="svg-file-text-bg" d="M48.037,56H7.963C7.155,56,6.5,55.345,6.5,54.537V39h43v15.537C49.5,55.345,48.845,56,48.037,56z"></path><text class="svg-file-ext" x="28" y="51.5">' + ext + '</text></svg>';
                    break;
                case 'pptx':
                case 'ppt':
                    icon = '<svg viewBox="0 0 56 56" class="svg-file svg-powerpoint files-svg"><path class="svg-file-bg" d="M36.985,0H7.963C7.155,0,6.5,0.655,6.5,1.926V55c0,0.345,0.655,1,1.463,1h40.074 c0.808,0,1.463-0.655,1.463-1V12.978c0-0.696-0.093-0.92-0.257-1.085L37.607,0.257C37.442,0.093,37.218,0,36.985,0z"></path><polygon class="svg-file-flip" points="37.5,0.151 37.5,12 49.349,12"></polygon><g class="svg-file-icon"><path style="fill:#c8bdb8" d="M39.5,30h-24V14h24V30z M17.5,28h20V16h-20V28z"></path><path style="fill:#c8bdb8" d="M20.499,35c-0.175,0-0.353-0.046-0.514-0.143c-0.474-0.284-0.627-0.898-0.343-1.372l3-5 c0.284-0.474,0.898-0.627,1.372-0.343c0.474,0.284,0.627,0.898,0.343,1.372l-3,5C21.17,34.827,20.839,35,20.499,35z"></path><path style="fill:#c8bdb8" d="M34.501,35c-0.34,0-0.671-0.173-0.858-0.485l-3-5c-0.284-0.474-0.131-1.088,0.343-1.372 c0.474-0.283,1.088-0.131,1.372,0.343l3,5c0.284,0.474,0.131,1.088-0.343,1.372C34.854,34.954,34.676,35,34.501,35z"></path><path style="fill:#c8bdb8" d="M27.5,16c-0.552,0-1-0.447-1-1v-3c0-0.553,0.448-1,1-1s1,0.447,1,1v3C28.5,15.553,28.052,16,27.5,16 z"></path><rect x="17.5" y="16" style="fill:#d3ccc9" width="20" height="12"></rect></g><path class="svg-file-text-bg" d="M48.037,56H7.963C7.155,56,6.5,55.345,6.5,54.537V39h43v15.537C49.5,55.345,48.845,56,48.037,56z"></path><text class="svg-file-ext" x="28" y="51.5">' + ext + '</text></svg>';
                    break;
                case 'mov':
                case 'mpeg':
                case 'mpg':
                case 'mp4':
                case 'm4v':
                    icon = '<svg viewBox="0 0 56 56" class="svg-file svg-video files-svg"><path class="svg-file-bg" d="M36.985,0H7.963C7.155,0,6.5,0.655,6.5,1.926V55c0,0.345,0.655,1,1.463,1h40.074 c0.808,0,1.463-0.655,1.463-1V12.978c0-0.696-0.093-0.92-0.257-1.085L37.607,0.257C37.442,0.093,37.218,0,36.985,0z"></path>\<polygon class="svg-file-flip" points="37.5,0.151 37.5,12 49.349,12"></polygon><g class="svg-file-icon"><path d="M24.5,28c-0.166,0-0.331-0.041-0.481-0.123C23.699,27.701,23.5,27.365,23.5,27V13 c0-0.365,0.199-0.701,0.519-0.877c0.321-0.175,0.71-0.162,1.019,0.033l11,7C36.325,19.34,36.5,19.658,36.5,20 s-0.175,0.66-0.463,0.844l-11,7C24.874,27.947,24.687,28,24.5,28z M25.5,14.821v10.357L33.637,20L25.5,14.821z"></path><path d="M28.5,35c-8.271,0-15-6.729-15-15s6.729-15,15-15s15,6.729,15,15S36.771,35,28.5,35z M28.5,7 c-7.168,0-13,5.832-13,13s5.832,13,13,13s13-5.832,13-13S35.668,7,28.5,7z"></path></g><path class="svg-file-text-bg" d="M48.037,56H7.963C7.155,56,6.5,55.345,6.5,54.537V39h43v15.537C49.5,55.345,48.845,56,48.037,56z"></path><text class="svg-file-ext" x="28" y="51.5">' + ext + '</text></svg>';
                    break;
                case 'wav':
                case 'mp3':
                case 'mp2':
                case 'm4a':
                case 'aac':
                    icon = '<svg viewBox="0 0 56 56" class="svg-file svg-audio files-svg"><path class="svg-file-bg" d="M36.985,0H7.963C7.155,0,6.5,0.655,6.5,1.926V55c0,0.345,0.655,1,1.463,1h40.074 c0.808,0,1.463-0.655,1.463-1V12.978c0-0.696-0.093-0.92-0.257-1.085L37.607,0.257C37.442,0.093,37.218,0,36.985,0z"></path><polygon class="svg-file-flip" points="37.5,0.151 37.5,12 49.349,12"></polygon><g class="svg-file-icon"><path d="M35.67,14.986c-0.567-0.796-1.3-1.543-2.308-2.351c-3.914-3.131-4.757-6.277-4.862-6.738V5 c0-0.553-0.447-1-1-1s-1,0.447-1,1v1v8.359v9.053h-3.706c-3.882,0-6.294,1.961-6.294,5.117c0,3.466,2.24,5.706,5.706,5.706 c3.471,0,6.294-2.823,6.294-6.294V16.468l0.298,0.243c0.34,0.336,0.861,0.72,1.521,1.205c2.318,1.709,6.2,4.567,5.224,7.793 C35.514,25.807,35.5,25.904,35.5,26c0,0.43,0.278,0.826,0.71,0.957C36.307,26.986,36.404,27,36.5,27c0.43,0,0.826-0.278,0.957-0.71 C39.084,20.915,37.035,16.9,35.67,14.986z M26.5,27.941c0,2.368-1.926,4.294-4.294,4.294c-2.355,0-3.706-1.351-3.706-3.706 c0-2.576,2.335-3.117,4.294-3.117H26.5V27.941z M31.505,16.308c-0.571-0.422-1.065-0.785-1.371-1.081l-1.634-1.34v-3.473 c0.827,1.174,1.987,2.483,3.612,3.783c0.858,0.688,1.472,1.308,1.929,1.95c0.716,1.003,1.431,2.339,1.788,3.978 C34.502,18.515,32.745,17.221,31.505,16.308z"></path></g><path class="svg-file-text-bg" d="M48.037,56H7.963C7.155,56,6.5,55.345,6.5,54.537V39h43v15.537C49.5,55.345,48.845,56,48.037,56z"></path><text class="svg-file-ext" x="28" y="51.5">' + ext + '</text></svg>';
                    break;
                case 'rtf':
                case 'txt':
                    icon = '<svg viewBox="0 0 56 56" class="svg-file svg-text files-svg"><path class="svg-file-bg" d="M36.985,0H7.963C7.155,0,6.5,0.655,6.5,1.926V55c0,0.345,0.655,1,1.463,1h40.074 c0.808,0,1.463-0.655,1.463-1V12.978c0-0.696-0.093-0.92-0.257-1.085L37.607,0.257C37.442,0.093,37.218,0,36.985,0z"></path><polygon class="svg-file-flip" points="37.5,0.151 37.5,12 49.349,12"></polygon><g class="svg-file-icon"><path d="M12.5,13h6c0.553,0,1-0.448,1-1s-0.447-1-1-1h-6c-0.553,0-1,0.448-1,1S11.947,13,12.5,13z"></path><path d="M12.5,18h9c0.553,0,1-0.448,1-1s-0.447-1-1-1h-9c-0.553,0-1,0.448-1,1S11.947,18,12.5,18z"></path><path d="M25.5,18c0.26,0,0.52-0.11,0.71-0.29c0.18-0.19,0.29-0.45,0.29-0.71c0-0.26-0.11-0.52-0.29-0.71 c-0.38-0.37-1.04-0.37-1.42,0c-0.181,0.19-0.29,0.44-0.29,0.71s0.109,0.52,0.29,0.71C24.979,17.89,25.24,18,25.5,18z"></path><path d="M29.5,18h8c0.553,0,1-0.448,1-1s-0.447-1-1-1h-8c-0.553,0-1,0.448-1,1S28.947,18,29.5,18z"></path><path d="M11.79,31.29c-0.181,0.19-0.29,0.44-0.29,0.71s0.109,0.52,0.29,0.71 C11.979,32.89,12.229,33,12.5,33c0.27,0,0.52-0.11,0.71-0.29c0.18-0.19,0.29-0.45,0.29-0.71c0-0.26-0.11-0.52-0.29-0.71 C12.84,30.92,12.16,30.92,11.79,31.29z"></path><path d="M24.5,31h-8c-0.553,0-1,0.448-1,1s0.447,1,1,1h8c0.553,0,1-0.448,1-1S25.053,31,24.5,31z"></path><path d="M41.5,18h2c0.553,0,1-0.448,1-1s-0.447-1-1-1h-2c-0.553,0-1,0.448-1,1S40.947,18,41.5,18z"></path><path d="M12.5,23h22c0.553,0,1-0.448,1-1s-0.447-1-1-1h-22c-0.553,0-1,0.448-1,1S11.947,23,12.5,23z"></path><path d="M43.5,21h-6c-0.553,0-1,0.448-1,1s0.447,1,1,1h6c0.553,0,1-0.448,1-1S44.053,21,43.5,21z"></path><path d="M12.5,28h4c0.553,0,1-0.448,1-1s-0.447-1-1-1h-4c-0.553,0-1,0.448-1,1S11.947,28,12.5,28z"></path><path d="M30.5,26h-10c-0.553,0-1,0.448-1,1s0.447,1,1,1h10c0.553,0,1-0.448,1-1S31.053,26,30.5,26z"></path><path d="M43.5,26h-9c-0.553,0-1,0.448-1,1s0.447,1,1,1h9c0.553,0,1-0.448,1-1S44.053,26,43.5,26z"></path></g><path class="svg-file-text-bg" d="M48.037,56H7.963C7.155,56,6.5,55.345,6.5,54.537V39h43v15.537C49.5,55.345,48.845,56,48.037,56z"></path><text class="svg-file-ext" x="28" y="51.5">' + ext + '</text></svg>';
                    break;
                case 'zip':
                case 'rar':
                case 'asice':
                case 'cdoc':
                    icon = '<svg viewBox="0 0 56 56" class="svg-file svg-archive files-svg"><path class="svg-file-bg" d="M36.985,0H7.963C7.155,0,6.5,0.655,6.5,1.926V55c0,0.345,0.655,1,1.463,1h40.074 c0.808,0,1.463-0.655,1.463-1V12.978c0-0.696-0.093-0.92-0.257-1.085L37.607,0.257C37.442,0.093,37.218,0,36.985,0z"></path><polygon class="svg-file-flip" points="37.5,0.151 37.5,12 49.349,12"></polygon><g class="svg-file-icon"><path d="M28.5,24v-2h2v-2h-2v-2h2v-2h-2v-2h2v-2h-2v-2h2V8h-2V6h-2v2h-2v2h2v2h-2v2h2v2h-2v2h2v2h-2v2h2v2 h-4v5c0,2.757,2.243,5,5,5s5-2.243,5-5v-5H28.5z M30.5,29c0,1.654-1.346,3-3,3s-3-1.346-3-3v-3h6V29z"></path><path d="M26.5,30h2c0.552,0,1-0.447,1-1s-0.448-1-1-1h-2c-0.552,0-1,0.447-1,1S25.948,30,26.5,30z"></path></g><path class="svg-file-text-bg" d="M48.037,56H7.963C7.155,56,6.5,55.345,6.5,54.537V39h43v15.537C49.5,55.345,48.845,56,48.037,56z"></path><text class="svg-file-ext" x="28" y="51.5">' + ext + '</text></svg>';
                    break;
                default:
                    icon = '<svg viewBox="0 0 56 56" class="svg-file svg-none files-svg"><path class="svg-file-bg" d="M36.985,0H7.963C7.155,0,6.5,0.655,6.5,1.926V55c0,0.345,0.655,1,1.463,1h40.074 c0.808,0,1.463-0.655,1.463-1V12.978c0-0.696-0.093-0.92-0.257-1.085L37.607,0.257C37.442,0.093,37.218,0,36.985,0z"></path><polygon class="svg-file-flip" points="37.5,0.151 37.5,12 49.349,12"></polygon><path class="svg-file-text-bg" d="M48.037,56H7.963C7.155,56,6.5,55.345,6.5,54.537V39h43v15.537C49.5,55.345,48.845,56,48.037,56z"></path><text class="svg-file-ext f_10" x="28" y="51.5">' + ext + '</text></svg>';
            }

            return icon
        }

        return this;
    }
})(jQuery);