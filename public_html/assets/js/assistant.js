$(document).ready(function ()
{
    function TemplatesBuilder(rawData)
    {
        this.rawData = rawData;

        this.build = function ()
        {
            var parts = this.rawData.split('---\n');

            for (var partId in parts)
            {
                var part = parts[partId];
                var firstSymbol = part.substr(0, 1);

                if (firstSymbol == '\n') {
                    part = part.substr(1, part.length - 1);
                }

                var lastSymbol = part.substr(part.length - 1, 1);

                if (lastSymbol == '\n') {
                    part = part.substr(0, part.length - 1);
                }

                parts[partId] = part;
            }

            console.log(parts);
            return this.rawData;
        };
    }

    $("#button-generate").click(function ()
    {
        var rawData = $("#textarea-raw-data").val();
        var templatesBuilder = new TemplatesBuilder(rawData);
        var templates = templatesBuilder.build();
        $("#textarea-templates").val(templates);
    });
});