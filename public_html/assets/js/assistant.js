$(document).ready(function ()
{
    function TemplatesBuilder(rawData)
    {
        this.rawData = rawData;

        this.build = function ()
        {
            var templates = '';
            var parts = this.getParts();

            for (var partId in parts) {
                var part = parts[partId];
                var subParts = part.split('\n');

                if (!(subParts.length > 0)) {
                    continue;
                }

                var template = '#' + subParts[0] + '{';

                for (var idx = 1; idx < subParts.length; idx++) {
                    template = template + subParts[idx] + ((idx < subParts.length - 1) ? '|' : '');
                }
                template = template + '}';
                templates = templates + template + '\n';
            }

            return templates;
        };

        this.getParts = function ()
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
            return parts;
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