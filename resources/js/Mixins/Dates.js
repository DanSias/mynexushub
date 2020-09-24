export default {
    data() {
        return {
            months: [
                'january',
                'february',
                'march',
                'april',
                'may',
                'june',
                'july',
                'august',
                'september',
                'october',
                'november',
                'december'
            ],

            monthDigits: [
                '01',
                '02',
                '03',
                '04',
                '05',
                '06',
                '07',
                '08',
                '09',
                '10',
                '11',
                '12'
            ],
            yearList: ['2016', '2017', '2018', '2019', '2020']
        }
    },

    computed: {
        calendarYear() {
            let d = new Date()
            let year = d.getFullYear()
            return year
        },
        calendarMonth() {
            let monthIndex = new Date().getMonth();
            let months = this.months;
            if (months && months[monthIndex]) {
                return months[monthIndex];
            }
        },
        currentYearMonth() {
            let year = this.calendarYear;
            let month = this.calendarMonth;
            let monthDigits = this.monthToDigits(month);
            return String(year) + monthDigits;
        },
        calendarYearMonth() {
            let d = new Date();
            let year = d.getFullYear();
            let monthIndex = d.getMonth();
            let month = monthIndex + 1;
            if (month < 10) {
                month = String('0') + String(month);
            }
            return String(year) + String(month);
        },
        calendarComplete() {
            let calendarMonth = this.calendarMonth;
            let months = this.months;

            let monthArray = [];
            for (let i = 0; i < months.length; i++) {
                const month = months[i];
                let passedCurrent = false;
                if (month == calendarMonth) {
                    break;
                }
                if (passedCurrent == false) {
                    monthArray.push(month.toLowerCase());
                }
            }
            return monthArray;
        },
        lastCompleteMonth() {
            let mo = this.calendarComplete;
            let lastComplete = mo[mo.length - 1];
            if (lastComplete === undefined) {
                lastComplete = 'december';
            }
            return lastComplete;
        },
        lastCalendarYearMonth() {
            let d = new Date();
            let year = d.getFullYear();
            let monthIndex = d.getMonth();
            if (monthIndex == 0) {
                year = year - 1;
                monthIndex = 12;
            }
            // Last Month
            let month = monthIndex;
            if (month < 10) {
                month = String('0') + String(month);
            }
            return String(year) + String(month);
        },
        rangeArray() {
            let array = [];
            let start = this.range.start;
            let end = this.range.end;

            let go = 0;

            let yearList = this.yearList;
            let moDigitList = this.monthDigits;
            for (const yr in yearList) {
                for (const mo in moDigitList) {
                    let yrmoString = yearList[yr] + moDigitList[mo];

                    if (yrmoString == start) {
                        go++;
                    }

                    if (go > 0) {
                        let yrmoInt = parseInt(yrmoString);
                        array.push(yrmoInt);
                    }

                    if (yrmoString == end) {
                        go = 0;
                    }
                }
            }
            return array;
        },

        monthsAgo() {
            let now = this.currentYearMonth;
            let start = parseInt(now);

            let array = [start];
            for (let i = 0; i < 12; i++) {
                start--;
                if (start % 100 == 0) {
                    start = start - 88;
                }
                array.push(start);
            }
            return array;
        }
    },

    methods: {
        setRangeStart(value) {
            this.range.start = value
            console.log('new range start: ' + value)
        },
        setRangeEnd(value) {
            this.range.end = value
            console.log('new range end: ' + value)
        },
        // Array of months in yearMonth format 
        buildRangeArray(start, end) {
            let array = [];
            let go = 0;
            if (start && ! end) {
                return [parseInt(start)];
            }
            let yearList = this.yearList;
            let moDigitList = this.monthDigits;
            for (const yr in yearList) {
                for (const mo in moDigitList) {
                    let yrmoString = String(yearList[yr]) + String(moDigitList[mo]);
                    if (yrmoString == start) {
                        go++;
                    }
                    if (go > 0) {
                        let yrmoInt = parseInt(yrmoString);
                        array.push(yrmoInt);
                    }
                    if (yrmoString == end) {
                        go = 0;
                    }
                }
            }
            return array;
        },

        // YearMonth digits in a given year
        yearDigits(year) {
            let monthDigits = this.monthDigits;
            let array = [];
            _.forEach(monthDigits, digit => {
                let string = String(year) + String(digit);
                array.push(string);
            });
            return array;
        },
        monthShortText(month) {
            month = parseInt(month);
            let index = month - 1;
            let months = this.months;
            let mo = months[index];
            let string = mo
                ? mo.charAt(0).toUpperCase() + mo.charAt(1) + mo.charAt(2)
                : '';
            return string;
        },
        yearMonthToText(yearMonth) {
            yearMonth = String(yearMonth);
            if (yearMonth == '') {
                return '';
            }
            let yr = yearMonth.substring(0, 4);
            let mo = yearMonth.slice(-2);
            let text = this.monthShortText(mo);
            return text + ' ' + yr;
        },

        twelveMonthsAgo(endMonth) {
            let end = parseInt(endMonth);
            let start = end - 99;
            if (start % 100 == 13) {
                start = String(start);
                let yr = start.substring(0, 4);
                yr = parseInt(yr) + 1;
                start = yr + '01';
                start = parseInt(start);
            }
            return String(start)
        },

        monthToDigits(month) {
            switch (month) {
                case 'january':
                    return '01'
                    break
                case 'february':
                    return '02'
                    break
                case 'march':
                    return '03'
                    break
                case 'april':
                    return '04'
                    break
                case 'may':
                    return '05'
                    break
                case 'june':
                    return '06'
                    break
                case 'july':
                    return '07'
                    break
                case 'august':
                    return '08'
                    break
                case 'september':
                    return '09'
                    break
                case 'october':
                    return '10'
                    break
                case 'november':
                    return '11'
                    break
                case 'december':
                    return '12'
                    break

                default:
                    break
            }
        },
    },
};
