<?php include ('header.php'); ?>

<tr>
	<td align="center" class="titleblock">
		<font size="2" face="<?php echo $emailDefaultFont ?>Open-sans, sans-serif" color="#555454">
			<span class="title"><?php echo t('Hi {firstname} {lastname},'); ?></span><br/>
			<span class="subtitle"><?php echo t('Thank you for creating a customer account at {shop_name}.'); ?></span>
		</font>
	</td>
</tr>
<tr>
	<td class="space_footer">&nbsp;</td>
</tr>
<tr>
	<td class="box" style="border:1px solid #D6D4D4;">
		<table class="table">
			<tr>
				<td width="10">&nbsp;</td>
				<td>
					<font size="2" face="<?php echo $emailDefaultFont ?>Open-sans, sans-serif" color="#555454">
						<p data-html-only="1" style="border-bottom:1px solid #D6D4D4;">
							<?php echo t('Your {shop_name} login details'); ?>
						</p>
						<span>
							<?php echo t('Here are your login details:'); ?><br />
							<span><strong><?php echo t('E-mail address:'); ?> <a href="mailto:{email}">{email}</a></strong></span>
						</span>
					</font>
				</td>
				<td width="10">&nbsp;</td>
			</tr>
		</table>
	</td>
</tr>
<tr>
	<td class="space_footer">&nbsp;</td>
</tr>
<tr>
	<td class="box" style="border:1px solid #D6D4D4;">
		<table class="table">
			<tr>
				<td width="10">&nbsp;</td>
				<td>
					<font size="2" face="<?php echo $emailDefaultFont ?>Open-sans, sans-serif" color="#555454">
						<p style="border-bottom:1px solid #D6D4D4;"><?php echo t('Important Security Tips:'); ?></p>
						<ol>
							<li><?php echo t('Always keep your account details safe.'); ?></li>
							<li><?php echo t('Never disclose your login details to anyone.'); ?></li>
							<li><?php echo t('Change your password regularly.'); ?></li>
							<li><?php echo t('Should you suspect someone is using your account illegally, please notify us immediately.'); ?></li>
						</ol>
					</font>
				</td>
				<td width="10">&nbsp;</td>
			</tr>
		</table>
	</td>
</tr>
<tr>
	<td class="space_footer">&nbsp;</td>
</tr>
<tr>
	<td class="linkbelow">
		<font size="2" face="<?php echo $emailDefaultFont ?>Open-sans, sans-serif" color="#555454">
			<span><?php echo t('You can now place orders on our shop:'); ?> <a href="{shop_url}">{shop_name}</a></span>
		</font>
	</td>
</tr>

<?php include ('footer.php'); ?>
