<template>
  <Datepicker
    v-model="date"
    @update:modelValue="handleInput"
    format="dd/MM/yyyy"
    :placeholder="placeholder"
    autoApply
  />
</template>

<script>
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

export default {
  components: { Datepicker },
  props: {
    placeholder: {
      type: String,
    },
    value: {
      type: Date,
    },
    name: {
      type: String,
    },
  },

  data() {
    return {
      date: null,
    };
  },

  methods: {
    handleInput(e) {
      this.$emit("input", { date: this.date, name: this.name });
    },
    format() {
      if (this.date) {
        const day = this.date.getDate();
        const month = this.date.getMonth() + 1;
        const year = this.date.getFullYear();
        return `${day < 10 ? "0" + day : day}/${
          month < 10 ? "0" + month : month
        }/${year}`;
      }
    },
  },

  watch: {
    value(newValue) {
      this.date = newValue;
    },
  },
};
</script>
