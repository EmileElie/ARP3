{*
* 2007-2018 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author     PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2018 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div id="dashboard" class="tab-pane {if $action == 'dashboard'}active{/if}">
  <div class="col-lg-6 mtarget-status">
    <form class="defaultForm form-horizontal col-lg-12" action="index.php?controller=AdminMtarget&token={$smarty.get.token|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data" novalidate="">
      <div class="panel">
        <div class="mtg panel-heading"><i class="icon-cogs"></i> {l s='Automated SMS' mod='mtarget'}</div>
        <div class="form-wrapper">
          {include file='./_partials/mtarget-message-table.tpl' withUpdateButtons=false all_messages=$all_messages lang=$lang}
        </div>
        <div class="panel-footer">
          <button type="submit" value="id" name="submitMtargetUpdateStatus" class="btn btn-default pull-right">
            <i class="process-icon-save"></i> {l s='Save' mod='mtarget'}
          </button>
        </div>
      </div>
    </form>
  </div>
  <div class="col-lg-6 bloc-img">
    <div id="titleChartContainer" style="height:24px;width:100%;">
        {l s='Distribution of sms by type' mod='mtarget'}
    </div>
    <div id="chartContainer" style="height:200px;width:100%;"></div>
    <div id="titleContainerLine" style="height:24px;width:100%;">
        {l s='Distribution of sms per month' mod='mtarget'}
    </div>
    <div id="chartContainerLine" style="height:200px;width:100%;"></div>
  </div>
</div>

<script src="https://d3js.org/d3-time.v1.min.js"></script>
<script src="https://d3js.org/d3-time-format.v2.min.js"></script>
<script type="text/javascript">
  {literal}
  window.onload = function () {
    var datapie = new Array(
        {label: "{/literal}{l s='New order' mod='mtarget'}{literal} {/literal}{$percent_new_order|floatval}{literal}%", value: {/literal}{$percent_new_order|floatval}{literal}  },
        {label: "{/literal}{l s='Order status' mod='mtarget'}{literal} {/literal}{$percent_order_statut|floatval}{literal}%", value: {/literal}{$percent_order_statut|floatval}{literal}  },
        {label: "{/literal}{l s='Abandoned cart' mod='mtarget'}{literal} {/literal}{$percent_cart|floatval}{literal}%", value: {/literal}{$percent_cart|floatval}{literal}  },
        {label: "{/literal}{l s='Account creation' mod='mtarget'}{literal} {/literal}{$percent_account|floatval}{literal}%", value: {/literal}{$percent_account|floatval}{literal}  },
        {label: "{/literal}{l s='Product return' mod='mtarget'}{literal} {/literal}{$percent_product_return|floatval}{literal}%", value: {/literal}{$percent_product_return|floatval}{literal}  },
        {label: "{/literal}{l s='Birthday' mod='mtarget'}{literal} {/literal}{$percent_birthday|floatval}{literal}%", value: {/literal}{$percent_birthday|floatval}{literal}  },
    );
    
    var dataline = {/literal}{$tab_stat|json_encode}{literal};

    //On affiche le donut que si il y a quelque chose à afficher.
    if ({/literal}{$empty_stat|intval}{literal} !== 1)
    {

        /* Premier graphique: la "donut" chart */
        var width = $("#chartContainer").width(),
            height = $("#chartContainer").height(),
            radius = Math.min(width, height) / 2;

        var svg = d3.select("#chartContainer")
            .append("svg")
            .attr("width", width)
            .attr("height", height)
            .append("g");

        svg.append("g")
            .attr("class", "slices");
        svg.append("g")
            .attr("class", "labels");
        svg.append("g")
            .attr("class", "lines");

        var pie = d3.layout.pie()
            .sort(null)
            .value(function(d) {
                return d.value;
            });

        var arc = d3.svg.arc()
            .outerRadius(radius * 0.8)
            .innerRadius(radius * 0.52);

        var outerArc = d3.svg.arc()
            .innerRadius(radius * 0.9)
            .outerRadius(radius * 0.9);

        svg.attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");

        var key = function(d){ return d.data.label; };

        var color = d3.scale.ordinal()
            .range(["#4f81bc", "#c0504e", "#9bbb58", "#23bfaa", "#8064a1", "#4aacc5"]);

        /* ------- Donut lui même ------- */
        var slice = svg.select(".slices").selectAll("path.slice")
            .data(pie(datapie), key);

        slice.enter()
            .insert("path")
            .style("fill", function(d) { return color(d.data.label); })
            .attr("class", "slice")
            .attr("visibility", function(d, i){
                if (d.data.value === 0) {
                    return "hidden";
                }
            });

        slice
            .transition().duration(1000)
            .attrTween("d", function(d) {
                this._current = this._current || d;
                var interpolate = d3.interpolate(this._current, d);
                this._current = interpolate(0);
                return function(t) {
                    return arc(interpolate(t));
                };
            })

        slice.exit()
            .remove();

        /* ------- Les labels ------- */

        var text = svg.select(".labels").selectAll("text")
            .data(pie(datapie), key);

        text.enter()
            .append("text")
            .attr("dy", ".35em")
            .text(function(d) {
                return d.data.label;
            })
            .attr("visibility", function(d, i){
                if (d.data.value === 0) {
                    return "hidden";
                }
            });

        function midAngle(d){
            return d.startAngle + (d.endAngle - d.startAngle)/2;
        }

        text.transition().duration(1000)
            .attrTween("transform", function(d) {
                this._current = this._current || d;
                var interpolate = d3.interpolate(this._current, d);
                this._current = interpolate(0);
                return function(t) {
                    var d2 = interpolate(t);
                    var pos = outerArc.centroid(d2);
                    pos[0] = radius * (midAngle(d2) < Math.PI ? 1 : -1);
                    return "translate("+ pos +")";
                };
            })
            .styleTween("text-anchor", function(d){
                this._current = this._current || d;
                var interpolate = d3.interpolate(this._current, d);
                this._current = interpolate(0);
                return function(t) {
                    var d2 = interpolate(t);
                    return midAngle(d2) < Math.PI ? "start":"end";
                };
            });

        text.exit()
            .remove();

        /* ------- Les lignes ------- */

        var polyline = svg.select(".lines").selectAll("polyline")
            .data(pie(datapie), key);

        polyline.enter()
            .append("polyline")
            .attr("visibility", function(d, i){
                if (d.data.value === 0) {
                    return "hidden";
                }
            });

        polyline.transition().duration(1000)
            .attrTween("points", function(d){
                this._current = this._current || d;
                var interpolate = d3.interpolate(this._current, d);
                this._current = interpolate(0);
                return function(t) {
                    var d2 = interpolate(t);
                    var pos = outerArc.centroid(d2);
                    pos[0] = radius * 0.95 * (midAngle(d2) < Math.PI ? 1 : -1);
                    return [arc.centroid(d2), outerArc.centroid(d2), pos];
                };
            });

        polyline.exit()
            .remove();

    }
    
    /* Début chart ligne */

    // Set the dimensions of the canvas / graph
    var width = $("#chartContainerLine").width() - 70,
        height = $("#chartContainerLine").height() - 30;

    // Parse the date / time
    var parseDate = d3.time.format("%Y-%m-%d").parse;
    var formatTime = d3.time.format("%B");

    // Set the ranges
    var x = d3.time.scale().range([0, width]);
    var y = d3.scale.linear().range([height, 0]);

    //We don't want d3 default behavior
    function customTimeFormat(date){
        return (d3.timeYear(date) < date ? d3.timeFormat("%b") : d3.timeFormat("%b %Y"))(date);
    }

    //We don't want to display float values and ticks if we have less then 4 messages sent
    var nbTicks = d3.max(dataline, function(d) { return d.sms; }) > 4 ? 4 : d3.max(dataline, function(d) { return d.sms; });

    // Define the axes
    var xAxis = d3.svg.axis().scale(x)
        .orient("bottom").tickFormat(customTimeFormat);

    var yAxis = d3.svg.axis().scale(y)
        .orient("left").ticks(nbTicks).tickFormat(d3.format("d"));

    // Define the line
    var valueline = d3.svg.line()
        .x(function(d) { return x(d.date); })
        .y(function(d) { return y(d.sms); });

    // Define the div for the tooltip
    var div = d3.select("#chartContainerLine").append("div")   
        .attr("class", "tooltip")               
        .style("opacity", 0);

    // Adds the svg canvas
    var svg = d3.select("#chartContainerLine")
        .append("svg")
            .attr("width", width)
            .attr("height", height)
        .append("g")
            .attr("transform", 
                  "translate(" + 37 + "," + 0 + ")");

    dataline.forEach(function(d) {
        d.date = parseDate(d.year + "-" + (d.month+1) + "-" + 1);
        d.sms = +d.sms;
    });

    // Scale the range of the data
    x.domain(d3.extent(dataline, function(d) { return d.date; }));
    y.domain([0, (d3.max(dataline, function(d) { return d.sms; })*1.20)]);

    // Add the X Axis
    svg.append("g")
        .attr("class", "x axis")
        .attr("transform", "translate(0," + height + ")")
        .call(xAxis);

    // Add the Y Axis
    svg.append("g")
        .attr("class", "y axis")
        .call(yAxis);

    // Horizontal grid
    svg.insert("g")
     .attr("class", "grid horizontal")
     .call(d3.svg.axis().scale(y)
        .orient("left")
        .ticks(nbTicks)
        .tickSize(-(width), 0, 0)
        .tickFormat("")
    );

    // Add the valueline path.
    svg.append("path")
        .attr("class", "line")
        .attr("d", valueline(dataline));

    // Add the scatterplot
    svg.selectAll("dot")    
        .data(dataline)         
    .enter().append("circle")                               
        .attr("r", 4)       
        .attr("cx", function(d) { return x(d.date); })       
        .attr("cy", function(d) { return y(d.sms); })     
        .on("mouseover", function(d) {      
            div.transition()        
                .duration(200)      
                .style("opacity", .9);      
            div .html(formatTime(d.date) + "<br/>"  + d.sms)  
                .style("left", ((d3.mouse(d3.select("#chartContainerLine").node())[0]) + 7) + "px")     
                .style("top", ((d3.mouse(d3.select("#chartContainerLine").node())[1]) - 42) + "px");
            $(this).attr("r", 6);
            })                  
        .on("mouseout", function(d) {       
            div.transition()        
                .duration(500)      
                .style("opacity", 0);   
            $(this).attr("r", 4);
        });

  }
  {/literal}
</script>
