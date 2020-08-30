import { render, fireEvent } from '@testing-library/svelte'
import { tick } from 'svelte'
import { event, t } from 'blessing-skin'
import UploadEditor from './UploadEditor.svelte'

test('submit data', () => {
  const { getByLabelText } = render(UploadEditor)
  fireEvent.input(getByLabelText(t('texture-description.description')), {
    target: { value: 'abcd' },
  })

  const form = new FormData()
  event.emit('beforeFetch', { data: form })
  expect(form.get('description')).toBe('abcd')
})

test('validate text length', async () => {
  const { getByLabelText, queryByText } = render(UploadEditor, {
    props: { maxLength: 1 },
  })
  fireEvent.input(getByLabelText(t('texture-description.description')), {
    target: { value: 'abcd' },
  })

  await tick()
  expect(
    queryByText(t('texture-description.exceeded', { max: 1 })),
  ).toBeInTheDocument()
})
