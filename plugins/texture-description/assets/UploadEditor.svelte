<script lang="ts">
  import { onMount, onDestroy } from 'svelte'
  import { event, t } from 'blessing-skin'

  let description = ''
  export let maxLength = Infinity

  onMount(() => {
    const off = event.on('beforeFetch', (request: { data: FormData }) => {
      request.data.set('description', description)
    })
    onDestroy(off)
  })
</script>

<label for="description-editor">{t('texture-description.description')}</label>
<textarea
  id="description-editor"
  class="form-control"
  rows="9"
  bind:value={description} />
{#if description.length > maxLength}
  <div class="alert alert-info mt-2">
    {t('texture-description.exceeded', { max: maxLength })}
  </div>
{/if}
