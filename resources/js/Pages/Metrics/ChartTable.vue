<template>
    <div class="mt-2">
        <table v-if="actualsArray.length > 0" class="table-fixed">
            <thead>
                <tr>
                    <th></th>
                    <th v-for="(month, index) in shortMonthsArray" :key="index" class="text-sm text-center">
                        {{ month }}
                    </th>
                    <th class="text-blue-500 text-sm text-center" v-if="metric != 'cpl'">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr class="hover:bg-yellow-50">
                    <td class="border px-2 py-1 text-sm text-semibold">Actuals</td>
                    <td v-for="(month, index) in monthsArray" :key="index" class="text-right text-sm border px-2 py-1">
                        <span v-if="money">$</span>{{ actualsArray[index] | bigCommas }}
                    </td>
                    <td class="text-right text-sm border px-4 py-2" v-if="metric != 'cpl'">
                        <span v-if="money">$</span>{{ arraySum(actualsArray) | bigCommas }} 
                    </td>
                </tr>
                <tr class="bg-gray-50 hover:bg-yellow-50">
                    <td class="border px-2 py-1 text-sm text-semibold">Budget</td>
                    <td v-for="(month, index) in monthsArray" :key="index" class="text-right text-sm border px-2 py-1">
                        <span v-if="money">$</span>{{ budgetArray[index] | bigCommas }}
                    </td>
                    <td class="text-right text-sm border px-4 py-2" v-if="metric != 'cpl'">
                        <span v-if="money">$</span>{{ arraySum(budgetArray) | bigCommas }}
                    </td>
                </tr>
                <tr class="hover:bg-yellow-50">
                    <td class="border px-2 py-1 text-sm text-semibold">Variance</td>
                    <td v-for="(month, index) in monthsArray" :key="index" class="text-right text-sm border px-2 py-1">
                        <span v-if="varianceArray[index] >= 0">
                            <span v-if="money">$</span>{{ varianceArray[index] | bigCommas }}
                        </span>
                        <span v-else>
                            -<span v-if="money">$</span>{{ Math.abs(varianceArray[index]) | bigCommas }}
                        </span>
                    </td>
                    <td class="text-right text-sm border px-2 py-1" v-if="metric != 'cpl'">
                        <span>
                             <span v-if="arraySum(varianceArray) >= 0">
                                <span v-if="money">$</span>{{ arraySum(varianceArray) | bigCommas }}
                            </span>
                            <span v-else>
                                -<span v-if="money">$</span>{{ Math.abs(arraySum(varianceArray)) | bigCommas }}
                            </span>
                        </span>
                    </td>
                </tr>
            </tbody>
        </table> 
    </div>
</template>

<script>
import FormatMetric from '../../Mixins/FormatMetric'

export default {
    name: 'metrics-chart-table',

    props: [
        'metric',
        'shortMonthsArray',
        'monthsArray',
        'actualsArray',
        'budgetArray',
        'varianceArray',
    ],

    mixins: [
        FormatMetric
    ],

    computed: {
        // true if a currenty metric
        money() {
            if (this.metric.toLowerCase() == 'spend' || this.metric.toLowerCase() == 'cpl') {
                return true;
            }
            return false;
        }
    },

    methods: {
        arraySum(array) {
            let sum = 0;
            _.forEach(array, el => {
                sum += el;
            });
            return sum;
        }
    }
}
</script>
