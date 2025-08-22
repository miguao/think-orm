import { defineComponent } from 'vue';
import { useFormItemRest } from '../../utils/hook';

export default defineComponent({
  name: 'FormItemRest',
  setup(_props, { slots }) {
    useFormItemRest();
    return () => slots.default?.();
  }
});
