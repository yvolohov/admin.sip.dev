$(document).ready(function()
{
    /* Общие методы для управления таблицами */
    function BaseManager()
    {
        this.CELL_TYPE_TEXT = 0;
        this.CELL_TYPE_EDIT = 1;
        this.CELL_TYPE_CHB = 2;

        /* Создает ячейку таблицы */
        this.createCell = function(cellType, cellData, inputId)
        {
            var cell = $(document.createElement("td"));

            if (cellType == this.CELL_TYPE_TEXT)
            {
                cell.html(cellData);
            }
            else if (cellType == this.CELL_TYPE_EDIT)
            {
                var div = $(document.createElement("div"));
                div.addClass("input-control input-text full-size");

                var input = $(document.createElement("input"));
                input.attr("type", "text");
                input.attr("name", inputId);
                input.val(cellData);

                div.append(input);
                cell.append(div);
            }
            else if (cellType == this.CELL_TYPE_CHB)
            {
                var label = $(document.createElement("label"));
                label.addClass("input-control input-checkbox small-check");

                var input = $(document.createElement("input"));
                input.addClass("templates_checkbox");
                input.attr("type", "checkbox");
                input.val("#templates_row_" + cellData);

                var span = $(document.createElement("span"));
                span.addClass("check");

                label.append(input);
                label.append(span);
                cell.append(label);
            }
            return cell;
        };
    }

    /* Управляет сообщениями об ошибках */
    function ErrorsManager()
    {
        this.fillErrors = function(errors)
        {
            var divMessages = $("#div-messages");

            $.each(errors, function(index, error)
            {
                var divMessage = $(document.createElement("div"));
                divMessage.addClass("div-message");
                divMessage.html(error);
                divMessages.append(divMessage);
            });
        };

        this.clearErrors = function()
        {
            $("#div-messages .div-message").remove();
        };
    }

    /* Управляет таблицей шаблонов */
    function TemplatesManager(templatesCount)
    {
        this.templatesCount = $("#templates_table tr[id ^= templates_row_]").length;

        /* Добавляет строку в конец таблицы */
        this.addTemplate = function()
        {
            var table = $("#templates_table");
            var row = $(document.createElement("tr"));
            var rowId = "templates_row_" + this.templatesCount;
            var inputsId = "templates[" + this.templatesCount + "]";
            var cellCheckbox = this.createCell(this.CELL_TYPE_CHB, this.templatesCount);
            var cellForeignTemplate = this.createCell(this.CELL_TYPE_EDIT, '', inputsId + "[foreign_template]");
            var cellNativeTemplate = this.createCell(this.CELL_TYPE_EDIT, '', inputsId + "[native_template]");

            row.attr("id", rowId);
            row.append([cellCheckbox, cellForeignTemplate, cellNativeTemplate]);
            table.append(row);
            this.templatesCount++;
        };

        /* Удаляет отмеченные строки из таблицы */
        this.removeTemplates = function()
        {
            var foundCheckboxes = $("#templates_table .templates_checkbox:checked");

            $.each(foundCheckboxes, function(index, foundCheckbox)
            {
                $(foundCheckbox.value).remove();
            });
        };

        /* Выбирает данные из таблицы */
        this.selectTemplates = function()
        {
            var foundInputs = $("#templates_table input[name ^= templates]");
            var templates = {};

            $.each(foundInputs, function(index, foundInput)
            {templates[foundInput.name] = foundInput.value;});

            return templates;
        };
    }

    /* Управляет таблицей предложений */
    function SentencesManager(templatesManager, errorsManager)
    {
        this.templatesManager = templatesManager;
        this.errorsManager = errorsManager;

        this.fillSentencesTable = function(sentences)
        {
            var table = $("#sentences_table");
            var self = this;

            $.each(sentences, function(index, rowData)
            {
                if (index == 0)
                {
                    $("#foreign_sentence").val(rowData.sentence1);
                    $("#native_sentence").val(rowData.sentence2);
                }
                var row = $(document.createElement("tr"));
                var rowId = "sentences_row_" + index;
                var inputsId = "sentences[" + index + "]";
                var cellRowNumber = self.createCell(self.CELL_TYPE_TEXT, index + 1);
                var cellForeignSentence = self.createCell(self.CELL_TYPE_EDIT, rowData.sentence1, inputsId + "[foreign_sentence]");
                var cellNativeSentence = self.createCell(self.CELL_TYPE_EDIT, rowData.sentence2, inputsId + "[native_sentence]");
                var cellParts = self.createCell(self.CELL_TYPE_EDIT, rowData.parts, inputsId + "[parts]");

                row.attr("id", rowId);
                row.append([cellRowNumber, cellForeignSentence, cellNativeSentence, cellParts]);
                table.append(row);
            });
        };

        this.clearSentencesTable = function()
        {
            $("#sentences_table tr[id ^= sentences_row_]").remove();
            $("#foreign_sentence").val("");
            $("#native_sentence").val("");
        };

        this.getSentences = function()
        {
            this.errorsManager.clearErrors();
            var self = this;

            $.ajax(
                {
                    type: "POST",
                    url: "/question/get-sentences/",
                    data: self.templatesManager.selectTemplates(),
                    success: function(data)
                    {
                        if (data && data.sentences)
                        {
                            self.clearSentencesTable();
                            self.fillSentencesTable(data.sentences);
                        }

                        if (data && data.errors)
                        {
                            self.errorsManager.fillErrors(data.errors);
                        }
                    },
                    error: function()
                    {
                        var errors = ["Request error !!!"];
                        self.errorsManager.fillErrors(errors);
                    }
                });
        };
    }

    var baseManager = new BaseManager();

    TemplatesManager.prototype = baseManager;
    SentencesManager.prototype = baseManager;

    var templatesManager = new TemplatesManager();
    var errorsManager = new ErrorsManager();
    var sentencesManager = new SentencesManager(templatesManager, errorsManager);

    /* Кнопка добавления шаблона в конец таблицы */
    $("#button-add-template").click(function()
    {
        templatesManager.addTemplate();
    });

    /* Кнопка удаления отмеченных шаблонов */
    $("#button-remove-templates").click(function()
    {
        templatesManager.removeTemplates();
    });

    /* Кнопка заполнения таблицы предложений */
    $("#button-fill-sentences").click(function()
    {
        sentencesManager.getSentences();
    });

    /* Кнопка очистки таблицы предложений */
    $("#button-clear-sentences").click(function()
    {
        sentencesManager.clearSentencesTable();
    });
});