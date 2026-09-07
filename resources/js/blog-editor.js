import {
    Alignment,
    Autoformat,
    AutoImage,
    AutoLink,
    Autosave,
    BlockQuote,
    Bold,
    ClassicEditor,
    Code,
    CodeBlock,
    Essentials,
    FindAndReplace,
    FontBackgroundColor,
    FontColor,
    FontFamily,
    FontSize,
    Fullscreen,
    GeneralHtmlSupport,
    Heading,
    Highlight,
    HorizontalLine,
    Image,
    ImageCaption,
    ImageInsert,
    ImageResize,
    ImageStyle,
    ImageTextAlternative,
    ImageToolbar,
    ImageUpload,
    Indent,
    IndentBlock,
    Italic,
    Link,
    LinkImage,
    List,
    ListProperties,
    Paragraph,
    PasteFromOffice,
    RemoveFormat,
    SelectAll,
    ShowBlocks,
    SimpleUploadAdapter,
    SourceEditing,
    SpecialCharacters,
    SpecialCharactersEssentials,
    Strikethrough,
    Subscript,
    Superscript,
    Table,
    TableCaption,
    TableCellProperties,
    TableColumnResize,
    TableProperties,
    TableToolbar,
    TextTransformation,
    Underline,
    WordCount,
} from 'ckeditor5';
import translations from 'ckeditor5/translations/vi.js';
import 'ckeditor5/ckeditor5.css';

const plugins = [
    Alignment, Autoformat, AutoImage, AutoLink, Autosave, BlockQuote, Bold, Code,
    CodeBlock, Essentials, FindAndReplace, FontBackgroundColor, FontColor, FontFamily,
    FontSize, Fullscreen, GeneralHtmlSupport, Heading, Highlight, HorizontalLine, Image,
    ImageCaption, ImageInsert, ImageResize, ImageStyle, ImageTextAlternative, ImageToolbar,
    ImageUpload, Indent, IndentBlock, Italic, Link, LinkImage, List, ListProperties,
    Paragraph, PasteFromOffice, RemoveFormat, SelectAll, ShowBlocks, SimpleUploadAdapter,
    SourceEditing, SpecialCharacters, SpecialCharactersEssentials, Strikethrough,
    Subscript, Superscript, Table, TableCaption, TableCellProperties, TableColumnResize,
    TableProperties, TableToolbar, TextTransformation, Underline, WordCount,
];

const toolbar = [
    'undo', 'redo', '|', 'sourceEditing', 'showBlocks', 'findAndReplace', 'selectAll', '|',
    'heading', '|', 'fontFamily', 'fontSize', 'fontColor', 'fontBackgroundColor', '|',
    'bold', 'italic', 'underline', 'strikethrough', 'subscript', 'superscript', 'code', 'removeFormat', '|',
    'alignment', '|', 'bulletedList', 'numberedList', 'outdent', 'indent', '|',
    'link', 'insertImage', 'insertTable', 'blockQuote', 'codeBlock', 'horizontalLine',
    'specialCharacters', 'highlight', '|', 'fullscreen',
];

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

document.querySelectorAll('[data-blog-ckeditor]').forEach(async (field) => {
    try {
        const editor = await ClassicEditor.create(field, {
            licenseKey: import.meta.env.VITE_CKEDITOR_LICENSE_KEY || 'GPL',
            language: 'vi',
            translations: [translations],
            plugins,
            toolbar: {
                items: toolbar,
                shouldNotGroupWhenFull: true,
            },
            placeholder: 'Bắt đầu viết bài blog của bạn...',
            heading: {
                options: [
                    { model: 'paragraph', title: 'Đoạn văn', class: 'ck-heading_paragraph' },
                    { model: 'heading1', view: 'h2', title: 'Tiêu đề lớn', class: 'ck-heading_heading1' },
                    { model: 'heading2', view: 'h3', title: 'Tiêu đề vừa', class: 'ck-heading_heading2' },
                    { model: 'heading3', view: 'h4', title: 'Tiêu đề nhỏ', class: 'ck-heading_heading3' },
                    { model: 'heading4', view: 'h5', title: 'Tiêu đề phụ', class: 'ck-heading_heading4' },
                ],
            },
            fontSize: {
                options: [10, 12, 14, 'default', 18, 20, 24, 30, 36],
                supportAllValues: true,
            },
            fontFamily: {
                options: [
                    'default',
                    'Arial, Helvetica, sans-serif',
                    'Georgia, serif',
                    'Tahoma, Geneva, sans-serif',
                    'Times New Roman, Times, serif',
                    'Courier New, Courier, monospace',
                ],
            },
            image: {
                insert: { integrations: ['upload', 'url'] },
                toolbar: [
                    'imageTextAlternative', 'toggleImageCaption', '|',
                    'imageStyle:inline', 'imageStyle:wrapText', 'imageStyle:breakText', '|',
                    'resizeImage', 'linkImage',
                ],
            },
            simpleUpload: {
                uploadUrl: field.dataset.uploadUrl,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
            },
            table: {
                contentToolbar: [
                    'tableColumn', 'tableRow', 'mergeTableCells', 'tableProperties',
                    'tableCellProperties', 'toggleTableCaption',
                ],
            },
            list: {
                properties: {
                    styles: true,
                    startIndex: true,
                    reversed: true,
                },
            },
            link: {
                addTargetToExternalLinks: true,
                defaultProtocol: 'https://',
            },
        });

        const wrapper = field.closest('.blog-ckeditor-wrapper');
        const status = wrapper?.querySelector('[data-blog-editor-status]');

        if (status) {
            status.appendChild(editor.plugins.get('WordCount').wordCountContainer);
        }

        editor.model.document.on('change:data', () => {
            field.value = editor.getData();
            wrapper?.classList.remove('has-error');
        });

        field.form?.addEventListener('submit', (event) => {
            field.value = editor.getData();

            if (!field.value.replace(/<[^>]*>/g, '').trim() && !/<img\b/i.test(field.value)) {
                event.preventDefault();
                wrapper?.classList.add('has-error');
                editor.editing.view.focus();
            }
        });
    } catch (error) {
        console.error('Không thể khởi tạo CKEditor cho bài viết blog.', error);
    }
});
