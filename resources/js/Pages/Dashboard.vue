<template>
  <AppLayout title="Dashboard">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
    </template>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="grid grid-cols-4 gap-4 mt-3 ml-2">
            <DatePicker
              id="date-picker-1"
              :value="startDate"
              @input="updateDate"
              name="start"
              placeholder="Select Date"
            />
            <DatePicker
              id="date-picker-2"
              :value="endDate"
              @input="updateDate"
              name="end"
              placeholder="Select End Date (optional)"
            />
            <Button @click="submit">Submit</Button>
          </div>
          <Chart :labels="chartLabels" :data="chartData" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import axios from "axios";
import AppLayout from "@/Layouts/AppLayout.vue";
import DatePicker from "@/Components/DatePicker.vue";
import Chart from "@/Components/Chart.vue";
import Button from "../../../vendor/laravel/jetstream/stubs/inertia/resources/js/Components/Button.vue";
export default {
  components: {
    AppLayout,
    DatePicker,
    Chart,
    Button,
  },

  data() {
    return {
      data: [],
      startDate: null,
      endDate: null,
      fetching: false,
    };
  },
  computed: {
    chartLabels() {
      return Object.keys(this.data);
    },
    chartData() {
      return Object.values(this.data);
    },
  },
  async created() {
    this.endDate = new Date();
    this.startDate = new Date(new Date().setDate(this.endDate.getDate() - 30));
    await this.fetchData();
  },

  methods: {
    submit() {
      this.fetchData();
    },
    async fetchData() {
      this.fetching = true;
      await axios
        .get(
          "http://localhost:8000/api/sales-per-day?start_date=" +
            this.dateFilter(this.startDate) +
            "&end_date=" +
            this.dateFilter(this.endDate)
        )
        .then((response) => {
          this.data = response.data;
          this.fetching = false;
        });
    },

    updateDate(e) {
      if (e.name == "start") {
        this.startDate = e.date;
      } else if (e.name == "end") {
        this.endDate = e.date;
      }
    },
    dateFilter(date) {
      let month = date.getMonth() + 1;
      let year = date.getFullYear();
      let day = date.getDate();
      return day + "-" + month + "-" + year;
    },
  },
};
</script>
