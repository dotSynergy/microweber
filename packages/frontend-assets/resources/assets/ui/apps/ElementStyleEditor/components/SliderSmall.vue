<template>
  <div class="mw-live-edit-slider-small form-control-live-edit-label-wrapper">
    <label  v-if="showLabel" class="live-edit-label mb-0 pt-1">{{ label }}
        <input
          type="number"
          class="form-control-input-range-slider"
          v-model.number="selectedValue"
          :min="min"
          :max="max"
          :step="step"
          @blur="validateValue"
        />
        <span>{{ unit }} </span>
    </label>
    <div data-size="medium" :class="{ 'col-12': !showLabel , 'col-12': showLabel }">
      <v-slider :min="min" :max="max" :step="step" v-model="selectedValue"></v-slider>
      <span @click="resetValue" class="reset-field tip  mw-action-buttons-background-circle-on-hover" data-tipposition="top-right" data-tip="Restore default value">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" height="16" viewBox="0 -960 960 960" width="16"><path d="M440-122q-121-15-200.5-105.5T160-440q0-66 26-126.5T260-672l57 57q-38 34-57.5 79T240-440q0 88 56 155.5T440-202v80Zm80 0v-80q87-16 143.5-83T720-440q0-100-70-170t-170-70h-3l44 44-56 56-140-140 140-140 56 56-44 44h3q134 0 227 93t93 227q0 121-79.5 211.5T520-122Z"></path></svg>
        </span>
    </div>
  </div>
</template>

<script>
export default {
  props: {
      showLabel: {
          type: Boolean,
          default: true
      },
    label: String,
    modelValue: Number, // Rename the prop to modelValue
    min: Number,
    max: Number,
    step: Number,
    unit: String, // Add the unit prop
  },
  data() {
    return {
      selectedValue: this.modelValue, // Use modelValue as the initial value
    };
  },
  methods: {
    resetValue() {
      this.selectedValue = null;
    },
    validateValue() {
      if (this.selectedValue !== null && this.selectedValue !== undefined) {
        if (this.min !== undefined && this.selectedValue < this.min) {
          this.selectedValue = this.min;
        }
        if (this.max !== undefined && this.selectedValue > this.max) {
          this.selectedValue = this.max;
        }
      }
    },
  },
  watch: {
    selectedValue(newValue) {
      // Only emit the 'update:modelValue' event if selectedValue is different from modelValue
      if (newValue !== this.modelValue) {
        this.$emit("update:modelValue", newValue);
      }
    },
    modelValue(newValue) {
      // Update selectedValue when the parent's v-model changes
      this.selectedValue = newValue;
    },
  },
};
</script>

<style scoped>
.mw-live-edit-slider-small {
  position: relative;
  padding: 8px 12px;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.01);
  border: none;
  transition: all 0.3s ease;
}

.mw-live-edit-slider-small:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.form-control-input-range-slider {
  width: 60px !important;
  height: 28px !important;
  padding: 4px 8px !important;
  border: none !important;
  border-bottom: 2px solid #e2e8f0 !important;
  border-radius: 0 !important;
  font-size: 12px !important;
  font-weight: 500 !important;
  color: #2d3748 !important;
  background: transparent !important;
  transition: all 0.3s ease !important;
  text-align: center !important;
  box-shadow: none !important;
}

.form-control-input-range-slider:focus {
  outline: none !important;
  border-bottom: 2px solid #4299e1 !important;
  background: rgba(66, 153, 225, 0.02) !important;
  transform: translateY(-1px) !important;
  box-shadow: 0 2px 8px rgba(66, 153, 225, 0.15) !important;
}

.form-control-input-range-slider:hover {
  border-bottom: 2px solid #cbd5e0 !important;
  background: rgba(0, 0, 0, 0.01) !important;
  transform: translateY(-0.5px) !important;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08) !important;
}

.form-control-input-range-slider::placeholder {
  color: #a0aec0 !important;
  font-size: 11px !important;
}

/* Chrome, Safari, Edge, Opera */
.form-control-input-range-slider::-webkit-outer-spin-button,
.form-control-input-range-slider::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
.form-control-input-range-slider[type=number] {
  -moz-appearance: textfield;
}

[data-size="medium"] {
  position: relative;
  padding: 4px 0;
}

.reset-field {
  position: absolute;
  right: 8px;
  top: 50%;
  transform: translateY(-50%);
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #a0aec0;
}

.reset-field:hover {
  color: #4a5568;
  background: rgba(0, 0, 0, 0.05);
  transform: translateY(-50%) scale(1.1);
}

.reset-field svg {
  transition: transform 0.2s ease;
}

.reset-field:hover svg {
  transform: rotate(180deg);
}
</style>
