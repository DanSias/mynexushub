export default {
    methods: { 
        formatNumber(value, type = '') {

            let initialValue = value;

            if (isNaN(value)) {
                return 0;
            }

            if (type == 'spend') {
                if (isNaN(value)) {
                    return initialValue;
                } else if (!isFinite(value)) {
                    return '$' + 0;
                } else if (value > 1000000) {
                    // Shorten if in millions
                    value = value / 1000000;
                    return '$' + value.toFixed(2) + 'M';
                } else if (value > 20000) {
                    // Shorten if 20k+
                    value = value / 1000;
                    return '$' + value.toFixed(1) + 'K';
                } else if (value < -1000000) {
                    value = value / 1000000;
                    return '-$' + Math.abs(value).toFixed(2) + 'M';
                } else if (value < -20000) {
                    value = value / 1000;
                    return '-$' + Math.abs(value).toFixed(1) + 'K';
                } else if (value < 0) {
                    value = Math.abs(value);
                    return '-$' + Math.round(value).toLocaleString();
                } else {
                    return '$' + Math.round(value).toLocaleString();
                }
            } else if (type == 'rate') {
                let pct = value * 100;
                if (!isFinite(pct)) {
                    return '0.0%';
                } else if (pct < 10) {
                    return pct.toFixed(2) + '%';
                } else {
                    return pct.toFixed(1) + '%';
                }
            } else {
                return Math.round(value).toLocaleString();
            }
        },

        formatMetric(value, metric = '') {
            if (['program', 'name', 'code'].includes(metric)) {
                return value
            }
            if (value === undefined || isNaN(value)) {
                return 0
            }
            let format = 0;
            switch (metric) {
                case '':
                case 'name':
                case 'program':
                case 'completeBudget':
                case 'completeForecast':
                    return value
                    break
                // round and add commas
                case 'leads':
                case 'leadsPace':
                case 'leadsBudget':
                case 'leadsBudgetPace':
                case 'leadsForecastPace':
                case 'leadsForecast':
                case 'leadsPaceDelta':
                case 'leadsForecastPaceDelta':
                case 'leadsBudgetDelta':
                case 'leadsForecastDelta':
                case 'leadsBudgetPaceDelta':
                case 'leadsForecastPaceDelta':
                case 'starts':
                case 'startsleadmonth':
                case 'startsleadmonth90':
                case 'startsleadmonth180':
                case 'students':
                case 'contact':
                case 'contactSum':
                case 'contact15':
                case 'quality':
                case 'qualitySum':
                case 'quality30':
                case 'start':
                case 'aip':
                case 'app30':
                case 'comfile':
                case 'comfile60':
                case 'acc':
                case 'acc90':
                case 'accconf':
                case 'accconf120':
                case 'students':
                case 'studentsBudget':
                    format = Math.round(value).toLocaleString()
                    break
                // add '$', round, and add commas
                case 'spend':
                case 'spendSum':
                case 'spendBudget':
                case 'spendBudgetSum':
                case 'spendForecast':
                case 'spendBudgetDelta':
                case 'spendForecastDelta':
                case 'spendBudgetSumDelta':
                case 'revenue':
                case 'revenueBudget':
                case 'revenueBudgetDelta':
                case 'margin':
                    format = this.formatNumber(value, 'spend')
                    // if (value >= 0) {
                    //     format = '$' + format
                    // } else if (value < 0) {
                    //     format = '-$' + format.replace('-', '')
                    // }
                    break;
                case 'ltv':
                case 'cpl':
                case 'cplBudget':
                case 'cplForecast':
                case 'cplBudgetDelta':
                case 'cplForecastDelta':
                case 'cplSum':
                case 'cplBudgetSum':
                case 'cplBudgetSumDelta':
                case 'cps':
                case 'cpc':
                case 'cpcontact':
                case 'cpcontact15':
                case 'cpcontactSum':
                case 'cpql':
                case 'cpquality':
                case 'cpqualitySum':
                case 'cpquality30':
                case 'cpstart':
                case 'cpstartsleadmonth':
                case 'cpstartsleadmonth90':
                case 'cpstartsleadmonth180':
                case 'cpaip':
                case 'cpapp30':
                case 'cpcomfile':
                case 'cpcomfile60':
                case 'cpacc':
                case 'cpacc90':
                case 'cpaccconf':
                case 'cpaccconf120':
                    let val = Math.round(value);
                    if (isNaN(val) || isNaN(Math.abs(val))) {
                        return 0;
                    } else if (val < 0) {
                        format = '-$' + Math.abs(val).toLocaleString()
                    } else {
                        format = '$' + val.toLocaleString()
                    }
                    break;
                // round, percent
                case 'leadsBudgetPercent':
                case 'leadsBudgetPacePercent':
                case 'leadsForecastPercent':
                case 'leadsForecastPacePercent':
                case 'spendBudgetPercent':
                case 'spendForecastPercent':
                case 'spendBudgetSumPercent':
                case 'cplBudgetPercent':
                    format = Math.round(value * 100).toLocaleString() + '%'
                    break;
                // 2 decimals, percent
                case 'cvrs':
                case 'contactRate':
                case 'contactRateSum':
                case 'contact15Rate':
                case 'qualityRate':
                case 'qualityRateSum':
                case 'quality30Rate':
                case 'quality30RateSum':
                case 'startRate':
                case 'startsleadmonthRate':
                case 'startsleadmonth90Rate':
                case 'startsleadmonth180Rate':
                case 'aipRate':
                case 'app30Rate':
                case 'comfileRate':
                case 'comfile60Rate':
                case 'accRate':
                case 'acc90Rate':
                case 'accconfRate':
                case 'accconf120Rate':
                case 'termConversion':
                case 'inSalesToAppRate':
                case 'startsAchieved':
                case 'studentsAchieved':
                case 'revenueAchieved':
                case 'mcor':
                case 'percent':
                    format = (value * 100).toFixed(2) + '%'
                    break;
                // 2 decimals, '$'
                // format = '$' + value.toFixed(2);
                // break;
                case 'ratio':
                    format = value.toFixed(2);
                    break;
                case 'lead mix':
                case 'pct1':
                    format = (value != 0) ? (value * 100).toFixed(1) + '%' : '0%'
                    break;
                case 'percent0':
                    format = Math.round(value * 100) + '%'
                    break;
                default:
                    format = value;
                    break;
            }
            if (! isFinite(value)) {
                return 'n/a';
            } else {
                return format;
            }
        },

        bigMoney(value, decimals = 2) {
            if (value > 10) {
                decimals = 1;
            }
            let returnValue = 0;
            let temp = 0;
            if (Math.abs(value) > 1000000) {
                temp = value / 1000000;
                if (value >= 0) {
                    returnValue = '$' + temp.toFixed(decimals) + 'M'
                } else {
                    returnValue = '-$' + Math.abs(temp).toFixed(decimals) + 'M';
                }
                return returnValue;
            } else {
                if (value >= 0) {
                    returnValue = '$' + Math.round(value).toLocaleString();
                } else {
                    returnValue = '-$' + Math.abs(Math.round(value)).toLocaleString();
                }
                return returnValue;
            }
        },
    },
};
