<script lang="ts">
  import { onMount, onDestroy } from 'svelte'
  import { event, t } from 'blessing-skin'

  let description = ''
  let maxLength = Infinity

  onMount(() => {
    const off = event.on('beforeFetch', (request: { data: FormData }) => {
      request.data.set('description', description)
    })
    onDestroy(off)
  })

  onMount(() => {
    const input = document.querySelector<HTMLInputElement>('#description-limit')
    maxLength = Number.parseInt(input?.value ?? '0') || Infinity
  })
</script>

<label for="description-editor">{t('texture-description.description')}</label>
<textarea
  id="description-editor"
  class="form-control"
  rows="9"
  bind:value={description} />
{#if description.length > maxLength}
  <div className="alert alert-info mt-2">
    {t('texture-description.exceeded', { max: maxLength })}
  </div>
{/if}
