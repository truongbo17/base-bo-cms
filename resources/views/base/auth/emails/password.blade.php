{{ trans('bo::base.click_here_to_reset') }}: <a href="{{ $link = bo_url('password/reset', $token).'?email='.urlencode($user->getEmailForPasswordReset()) }}"> {{ $link }} </a>
