<template>
    <div class="flex flex-wrap">
        <div class="w-full text-center">
            <button ref="trigger" v-on:mouseenter="togglePopover()" v-on:mouseleave="togglePopover()" class="text-gray-300 hover:text-gray-500 active:text-gray-800  text-sm px-6 py-3 outline-none focus:outline-none mr-1 mb-1" type="button" style="transition:all .15s ease">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
            </button>
            <div 
                ref="pop" 
                :class="(showPopover) ? 'block' : 'hidden'" 
                class="border border-gray-200 mt-1 block z-50 text-sm shadow-xl text-left no-underline break-words "
            >
                <div>
                    <div class="bg-gray-50 text-gray-800 font-semibold p-3 mb-0 border-b border-solid border-gray-200 capitalize">
                        Progress Table
                    </div>
                    <div class="bg-white p-6 max-w-lg">
                        <p>Forecast progress of all expected channels for each program, based on budget and existing forecast.</p>
                        <p class="mt-2">Click on a program to see all channel forecasts for that program, click on the channel to see initiative level inputs for that channel.</p>
                        <p class="mt-2"><span class="text-blue-500 font-bold">Blue Channels</span> have been submitted by the channel owner and are awaiting approval.</p>
                        <p class="mt-2"><span class="text-green-500 font-bold">Green Channels</span> have been approved by the ADMA.</p>
                    </div>
                </div>
            </div>
        </div>
        <div>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras id urna commodo, accumsan tortor id, vehicula lacus. In et dolor finibus, vestibulum dolor eu, suscipit massa. Donec ornare metus luctus magna consequat, sed condimentum nunc gravida. Donec erat eros, pretium eu orci ut, porta lobortis nibh. Vivamus cursus lobortis arcu, convallis rhoncus metus lobortis non. Proin pulvinar nec tellus malesuada auctor. Sed eget est tellus. Integer ac rhoncus magna. Donec vehicula malesuada turpis. Sed id mi diam.

Quisque scelerisque aliquam diam sed pharetra. Morbi semper non justo at tristique. Donec tincidunt est eget porta pharetra. Fusce dapibus tellus vitae eros euismod rhoncus. Nunc porta nulla eget viverra semper. Nunc eu pharetra justo, id sollicitudin augue. Nulla ut arcu et turpis laoreet cursus sit amet maximus est. Donec vitae ipsum urna.

Maecenas in neque non purus tincidunt luctus. Curabitur dui sem, scelerisque vitae velit convallis, posuere porta quam. Nulla et massa risus. Phasellus ultrices nunc interdum mauris cursus tincidunt. Quisque blandit dui sed laoreet commodo. Curabitur condimentum finibus velit. Mauris vitae sollicitudin tellus. Nullam sit amet arcu ut dolor mollis elementum et sed nisl. Mauris lectus lacus, suscipit vitae ligula et, vestibulum pulvinar magna. Proin vel odio fermentum, feugiat elit in, semper enim. Vivamus elit ante, imperdiet in hendrerit at, condimentum ut leo. Morbi pharetra erat a quam egestas, interdum convallis mi pulvinar. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam suscipit molestie sem sit amet faucibus. Aliquam leo sem, blandit nec massa vel, dictum tristique lorem. Integer molestie vehicula neque ut vehicula.

Nulla ac porttitor sem, eu hendrerit magna. Phasellus fermentum massa nec est lobortis rhoncus. Nulla ornare nisl quis ornare tincidunt. Mauris ut diam ut turpis posuere tincidunt. Vestibulum scelerisque pulvinar velit, eu pretium ex mollis et. Ut placerat sollicitudin eros, vel vestibulum ligula rutrum nec. Suspendisse vestibulum ex quis viverra tristique.

Ut in neque ultricies, egestas leo nec, dignissim leo. Aliquam fringilla porttitor ex, eget efficitur arcu vestibulum eget. Morbi neque sem, hendrerit eu ligula sit amet, luctus placerat sem. Donec viverra lacinia tincidunt. Curabitur fringilla sem vel purus cursus, eu lacinia augue rutrum. Nunc eu lectus lacus. Maecenas faucibus elit sed dapibus aliquam. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse potenti. Suspendisse nec nisl felis. Mauris viverra, dolor et luctus mattis, ligula ipsum gravida ante, id consectetur diam risus
        </div>
  </div>
</template>

<script>
import Popper from "popper.js";
import ChannelSelect from '../Components/Select/Channel'
import ProgramSelect from '../Components/Select/Program'

export default {
    name: 'NexusTest',

    data() {
        return {
            action: 'add',
            actions: ['add', 'scale', 'distribute'],
            showPopover: false,
            link: {
                program: '',
                channel: ''
            }
        }
    },

    components: {
        ChannelSelect,
        ProgramSelect
    },

    methods: {
        setRange(range) {
            console.log('testing the range for ' + range)
        },
        togglePopover() {
            if(this.showPopover){
                this.showPopover = false
            } else {
                this.showPopover = true
                new Popper(this.$refs.trigger, this.$refs.pop, {
                    placement: "right"
                })
            }
        }
    }
}
</script>

<style>

</style>