{{-- Add this in your layout's <head> if not already included --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Dropzone CSS --}}
<link rel="stylesheet" href="{{ asset('public/backEnd/dropzone/dropzone.min.css') }}">

    <style>
        .gap-8{
            gap: 8px;
        }
        .lineH1{
            line-height: 1;
        }
        .dropzone {
            min-height: 160px;
            background: #EEEEEE;
            border: 1px dashed #827D93;
            border-radius: 8px;
            position: relative;
        }

        .dz-default.dz-message{
            display: none;
        }

        .dropzone-placeholder {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }

        .dropzone-placeholder * {
            display: block;
            text-align: center;
            margin: 0 auto;
        }

        .dropzone-placeholder svg {
            max-width: 45px;
            margin-bottom: 15px;
        }

        .dropzone-placeholder h5 {
            font-weight: 700;
            font-size: 16px;
            line-height: 1.3;
            color: #7A5FEC;
        }

        .dropzone-placeholder p {
            font-weight: 400;
            font-size: 16px;
            line-height: 130%;
            color: #19213D;
            margin-top: 5px;
        }
        .dropzone.dz-drag-hover {
            background: #03A9F4;
        }

        .dropzone-uploads {
            
        }

        .dropzone-uploads__inner {
            padding: 15px 0;
        }

        .upload-item-content{
            background: #ffffff;
            box-shadow: 0px 4px 16px 0px #0000001A;
            border-radius: 3px;
            padding: 8px;
            display: flex;
            gap: 12px;
        }

        .upload-item-content .info{
            max-width: calc(100% - 62px);
            width: calc(100% - 62px);
        }

        .upload-item__error-message {
            /* min-width: 150px; */
            text-align: center;
            display: none;
        }

        .progress {
            position: relative;
            height: 8px;
            display: block;
            background-color: #E3E3ED;
            border-radius: 8px;
            overflow: hidden;
            width: 100%;
        }

        .progress__inner {
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            background-color: #26a69a;
            transition: width 0.3s linear;
        }
        .upload-item-file-icon{
            min-width: 48px;
            width: 48px;
            height: 56px;
            border-radius: 4px;
            background: #E9E3F8;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .upload-item .info .left{
            width: calc(100% - 20px);
        }

        span.upload-item__name {
            display: block;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            width: 100%;
            font-weight: 700;
            font-size: 14px;
            line-height: 1.3;
            vertical-align: middle;
            color: #575361;
        }

        .upload-item__size,
        .upload-item__size_progress{
            font-weight: 500;
            font-size: 12px;
            line-height: 1.3;
            color: #857E95;
        }

        .upload-item__status {
            font-weight: 500;
            font-size: 12px;
            line-height: 1;
            color: #9892A6;
        }

        .upload-item.dz-success .upload-item__status {
            color: #4E884D;
        }

        .upload-item__error-message{
            font-weight: 500;
            font-size: 12px;
            line-height: 1;
            color: #E36363;
        }

        /* color 1 */

        .upload-item.dz-processing .upload-item-file-icon{
            background: #E9E3F8;
        }

        .upload-item.dz-processing .upload-item-file-icon svg{
            color: #AC96E4
        }

        .upload-item.dz-processing .progress__inner{
            background: linear-gradient(90deg, rgba(58, 97, 237, 0.52) 0%, #7C3AED 100%);
        }

        /* color 2 */

        .upload-item.dz-success .upload-item-file-icon{
            background: #DAF2D9;
        }

        .upload-item.dz-success .upload-item-file-icon svg{
            color: #73B172
        }

        .upload-item.dz-success .progress__inner{
            background: #73B172;
        }

        /* color 3 */

        .upload-item.dz-error .upload-item-file-icon{
            background: #F2D9D9;
        }

        .upload-item.dz-error .upload-item-file-icon svg{
            color: #E36363
        }

        .upload-item.dz-error .progress__inner{
            background: #E3E3ED;
        }

        .dropzone-uploads .action a{
            display: inline-block;
        }

        .dropzone-uploads .action a:hover {
            transform: scale(1.2) rotate(360deg);
        }
        
    </style>
<script src="{{ asset('public/backEnd/dropzone/dropzone.min.js') }}"></script>
<script src="{{ asset('public/backEnd/js/custom.js') }}"></script>

<div class="modal-body">
    <div class="container-fluid">

        {{-- Form with Dropzone --}}
        {{ html()->form('POST', route('upload-homework-content'))->attributes([
            'class' => 'form-horizontal',
            'files' => true,
            'enctype' => 'multipart/form-data',
        ])->open() }}

        <input type="hidden" name="id" value="{{ $homework_id }}">

        <div class="row">
            <div class="col-lg-12 mt-30">
                <div class="dropzone" id="fileDropZone">
                    <div class="dropzone-placeholder">
                        <svg width="46" height="34" viewBox="0 0 46 34" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M45.5001 16.9995C45.5095 20.5717 44.3506 24.049 42.2001 26.9014C42.0816 27.059 41.9333 27.1917 41.7636 27.292C41.5939 27.3922 41.4061 27.4581 41.2109 27.4858C41.0157 27.5134 40.817 27.5024 40.6261 27.4533C40.4352 27.4042 40.2558 27.3179 40.0982 27.1995C39.9406 27.0811 39.8079 26.9328 39.7076 26.763C39.6074 26.5933 39.5415 26.4055 39.5138 26.2103C39.4861 26.0151 39.4972 25.8164 39.5463 25.6255C39.5954 25.4346 39.6816 25.2552 39.8001 25.0976C41.5604 22.7656 42.5087 19.9213 42.5001 16.9995C42.5001 13.4191 41.0778 9.9853 38.546 7.45356C36.0143 4.92182 32.5805 3.4995 29.0001 3.4995C25.4196 3.4995 21.9859 4.92182 19.4541 7.45356C16.9224 9.9853 15.5001 13.4191 15.5001 16.9995C15.5001 17.3973 15.342 17.7789 15.0607 18.0602C14.7794 18.3415 14.3979 18.4995 14.0001 18.4995C13.6022 18.4995 13.2207 18.3415 12.9394 18.0602C12.6581 17.7789 12.5001 17.3973 12.5001 16.9995C12.4993 15.4858 12.7069 13.9791 13.1169 12.522C12.9126 12.4995 12.7063 12.4995 12.5001 12.4995C10.1131 12.4995 7.82394 13.4477 6.13611 15.1355C4.44828 16.8234 3.50007 19.1126 3.50007 21.4995C3.50007 23.8865 4.44828 26.1756 6.13611 27.8635C7.82394 29.5513 10.1131 30.4995 12.5001 30.4995H17.0001C17.3979 30.4995 17.7794 30.6575 18.0607 30.9388C18.342 31.2201 18.5001 31.6017 18.5001 31.9995C18.5001 32.3973 18.342 32.7789 18.0607 33.0602C17.7794 33.3415 17.3979 33.4995 17.0001 33.4995H12.5001C10.8504 33.4999 9.21837 33.1601 7.7059 32.5014C6.19344 31.8427 4.83303 30.8793 3.70965 29.6712C2.58626 28.4632 1.72402 27.0364 1.17679 25.4802C0.629558 23.9239 0.409082 22.2715 0.529132 20.6262C0.649183 18.9809 1.10718 17.3781 1.87452 15.9177C2.64187 14.4574 3.70207 13.1709 4.98892 12.1387C6.27577 11.1065 7.76162 10.3508 9.35368 9.91859C10.9457 9.48643 12.6098 9.38717 14.2419 9.627C15.9039 6.30292 18.6395 3.63727 22.0054 2.06181C25.3714 0.486358 29.1706 0.0933736 32.7878 0.946514C36.405 1.79965 39.6282 3.84895 41.9354 6.76247C44.2426 9.67599 45.4986 13.2831 45.5001 16.9995ZM28.5613 15.9383C28.422 15.7988 28.2566 15.6882 28.0745 15.6127C27.8924 15.5372 27.6972 15.4983 27.5001 15.4983C27.3029 15.4983 27.1078 15.5372 26.9257 15.6127C26.7436 15.6882 26.5781 15.7988 26.4388 15.9383L20.4388 21.9383C20.2995 22.0776 20.1889 22.2431 20.1135 22.4252C20.0381 22.6072 19.9992 22.8024 19.9992 22.9995C19.9992 23.1966 20.0381 23.3918 20.1135 23.5738C20.1889 23.7559 20.2995 23.9214 20.4388 24.0608C20.7203 24.3422 21.102 24.5003 21.5001 24.5003C21.6972 24.5003 21.8923 24.4615 22.0744 24.3861C22.2565 24.3107 22.422 24.2001 22.5613 24.0608L26.0001 20.6201V31.9995C26.0001 32.3973 26.1581 32.7789 26.4394 33.0602C26.7207 33.3415 27.1022 33.4995 27.5001 33.4995C27.8979 33.4995 28.2794 33.3415 28.5607 33.0602C28.842 32.7789 29.0001 32.3973 29.0001 31.9995V20.6201L32.4388 24.0608C32.5782 24.2001 32.7436 24.3107 32.9257 24.3861C33.1078 24.4615 33.303 24.5003 33.5001 24.5003C33.6972 24.5003 33.8923 24.4615 34.0744 24.3861C34.2565 24.3107 34.422 24.2001 34.5613 24.0608C34.7007 23.9214 34.8112 23.7559 34.8867 23.5738C34.9621 23.3918 35.0009 23.1966 35.0009 22.9995C35.0009 22.8024 34.9621 22.6072 34.8867 22.4252C34.8112 22.2431 34.7007 22.0776 34.5613 21.9383L28.5613 15.9383Z" fill="#7C3AED"/>
                        </svg>
                        <h5>Browse File</h5>
                        <p> Or Drag & Drop File Here ({{generalSetting()->file_size / 1024}} Mb max file size)</p>
                    </div>
                </div>

                <div class="dropzone-uploads">
                    
                    <div class="dropzone-uploads__inner row row-gap-24" id="previews">
                        <div class="upload-item col-xl-6 col-md-6 col-lg-6 d-none" id="uploadItemTemplate">
                            <div class="upload-item-content">
                                <span class="upload-item-file-icon">
                                    <svg width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M17.0306 5.71938L11.7806 0.469375C11.7109 0.399749 11.6282 0.344539 11.5371 0.306898C11.4461 0.269257 11.3485 0.249923 11.25 0.25H2.25C1.85218 0.25 1.47064 0.408035 1.18934 0.68934C0.908035 0.970645 0.75 1.35218 0.75 1.75V18.25C0.75 18.6478 0.908035 19.0294 1.18934 19.3107C1.47064 19.592 1.85218 19.75 2.25 19.75H15.75C16.1478 19.75 16.5294 19.592 16.8107 19.3107C17.092 19.0294 17.25 18.6478 17.25 18.25V6.25C17.2501 6.15148 17.2307 6.05391 17.1931 5.96286C17.1555 5.87182 17.1003 5.78908 17.0306 5.71938ZM11.25 6.25V2.125L15.375 6.25H11.25Z" fill="currentColor"/>
                                    </svg>
                                </span>
                                <div class="info">
                                    <div class="d-flex justify-content-between gap-8">
                                        <div class="left">
                                            <span class="upload-item__name" data-dz-name></span>
                                            <span class="upload-item__size" data-dz-size></span>
                                        </div>
                                        <div class="action">
                                            <a href="#">
                                            <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M9.85378 9.14653C9.90024 9.19298 9.93709 9.24813 9.96223 9.30883C9.98737 9.36952 10.0003 9.43458 10.0003 9.50028C10.0003 9.56597 9.98737 9.63103 9.96223 9.69172C9.93709 9.75242 9.90024 9.80757 9.85378 9.85403C9.80733 9.90048 9.75218 9.93733 9.69148 9.96247C9.63078 9.98761 9.56573 10.0006 9.50003 10.0006C9.43433 10.0006 9.36928 9.98761 9.30858 9.96247C9.24789 9.93733 9.19274 9.90048 9.14628 9.85403L5.00003 5.70715L0.853784 9.85403C0.759963 9.94785 0.632716 10.0006 0.500034 10.0006C0.367352 10.0006 0.240104 9.94785 0.146284 9.85403C0.0524635 9.76021 -0.000244138 9.63296 -0.000244141 9.50028C-0.000244143 9.36759 0.0524635 9.24035 0.146284 9.14653L4.29316 5.00028L0.146284 0.854028C0.0524635 0.760208 -0.000244141 0.63296 -0.000244141 0.500278C-0.000244141 0.367596 0.0524635 0.240348 0.146284 0.146528C0.240104 0.0527077 0.367352 0 0.500034 0C0.632716 0 0.759963 0.0527077 0.853784 0.146528L5.00003 4.2934L9.14628 0.146528C9.2401 0.0527077 9.36735 -2.61548e-09 9.50003 0C9.63271 2.61548e-09 9.75996 0.0527077 9.85378 0.146528C9.9476 0.240348 10.0003 0.367596 10.0003 0.500278C10.0003 0.63296 9.9476 0.760208 9.85378 0.854028L5.70691 5.00028L9.85378 9.14653Z" fill="#794FED"/>
                                            </svg>
                                            </a>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="col-lg-12 text-center mt-40">
                <div class="mt-40 d-flex justify-content-between">
                    <button type="button" class="primary-btn tr-bg submit" data-dismiss="modal">@lang('common.cancel')
                    </button>

                    <button class="primary-btn fix-gr-bg" type="submit">@lang('common.save')
                    </button>
                </div>
            </div>
        </div>

        {{ html()->form()->close() }}
    </div>
</div>

<script>
    Dropzone.autoDiscover = false;

    $(document).ready(function () {
        const previewNode = document.querySelector("#uploadItemTemplate");
        previewNode.classList.remove("d-none");
        previewNode.id = "";

        const previewTemplate = previewNode.parentNode.innerHTML;
        previewNode.parentNode.removeChild(previewNode);

        // Initialize Dropzone on the #fileDropZone element
        let fileDropZoneEl = new Dropzone("#fileDropZone", {
            url: '/Home/UploadFile',
            maxFilesize: 20,
            previewTemplate: previewTemplate,
            autoQueue: true,
            previewsContainer: "#previews",
            thumbnailWidth: 40,
            thumbnailHeight: 40,
            dictDefaultMessage: "",
            autoProcessQueue: false,
        });

        fileDropZoneEl.on("addedfile", function(file) {
            // Create a hidden input[type="file"] for each file
            let input = document.createElement('input');
            input.type = 'file';
            input.name = 'files[]';
            input.classList.add('dz-hidden-input');
            input.style.display = 'none';

            // Use DataTransfer to set the File object to the input
            let dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            input.files = dataTransfer.files;

            // Attach to the preview element for easy removal
            if (file.previewElement) {
                file.previewElement.appendChild(input);
                file._hiddenInput = input;
            }
        });

        // Remove hidden input when file is removed
        fileDropZoneEl.on("removedfile", function(file) {
            if (file._hiddenInput && file._hiddenInput.parentNode) {
                file._hiddenInput.parentNode.removeChild(file._hiddenInput);
            }
        });

        // Handle removing files
        $('#previews').on('click', '.action a', function (e) {
            e.preventDefault();
            const previewEl = $(this).closest('.upload-item')[0];

            // Remove file from Dropzone
            if (previewEl && previewEl.dropzoneFile) {
                fileDropzone.removeFile(previewEl.dropzoneFile);
            } else {
                $(previewEl).remove(); // If fallback
            }
        });
    });
</script>

