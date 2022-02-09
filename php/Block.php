<?php
/**
 * Block class.
 *
 * @package SiteCounts
 */

namespace XWP\SiteCounts;

use WP_Block;
use WP_Query;

/**
 * The Site Counts dynamic block.
 *
 * Registers and renders the dynamic block.
 */
class Block {

	/**
	 * The Plugin instance.
	 *
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * Instantiates the class.
	 *
	 * @param Plugin $plugin The plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Adds the action to register the block.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', [ $this, 'register_block' ] );
	}

	/**
	 * Registers the block.
	 */
	public function register_block() {
		register_block_type_from_metadata(
			$this->plugin->dir(),
			[
				'render_callback' => [ $this, 'render_callback' ],
			]
		);
	}

	/**
	 * Renders the block.
	 *
	 * @param array $attributes The attributes for the block.
	 * @return string The markup of the block.
	 */
	public function render_callback( array $attributes ): string {
		$post_types = get_post_types( [ 'public' => true ], 'objects' );
		$class_name = $attributes['className'];
		ob_start();

		?>
		<div class="<?php echo esc_attr( $class_name ); ?>">
			<h2>
			<?php
				esc_html_e( 'Post Counts', 'site-counts' );
			?>
			</h2>
			<ul>
			<?php
			foreach ( $post_types as $post_type ) {
				$post_count = wp_count_posts( $post_type->name );
				$count      = $post_count->publish + $post_count->inherit;
				$single     = $post_type->labels->singular_name;
				$plural     = $post_type->labels->name;
				?>
				<li>
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: Number of posts 2: Singular name 3: Plural name */
							_n( // phpcs:ignore WordPress.WP.I18n.MismatchedPlaceholders
								'There is %1$d %2$s.',
								'There are %1$d %3$s.',
								$count,
								'site-counts'
							),
							$count,
							$single,
							$plural
						)
					);
					?>
				</li>
				<?php
			}
			?>
			</ul>
			<p>
			<?php
			$current_id = get_the_ID();
			if ( $current_id ) {
				echo esc_html(
					sprintf(
						/* translators: %d: Current post ID */
						__( 'The current post ID is %d.', 'site-counts' ),
						$current_id
					)
				);
			}
			?>
			</p>

			<?php
			$query = new WP_Query(
				[
					'post_type'      => [ 'post', 'page' ],
					'post_status'    => 'any',
					'posts_per_page' => 6,
					'tag'            => 'foo',
					'category_name'  => 'baz',
				]
			);

			if ( $query->have_posts() ) {
				?>
				<h2>
				<?php
					esc_html_e( '5 posts with the tag of foo and the category of baz', 'site-counts' );
				?>
				</h2>
				<ul>
				<?php
				$posts = wp_list_filter( $query->posts, [ 'ID' => get_the_ID() ], 'NOT' );
				$posts = array_slice( $posts, 0, 5 );
				foreach ( $posts as $post ) {
						echo '<li>' . esc_html( $post->post_title ) . '</li>';
				}
			}
			?>
			</ul>
		</div>
		<?php

		return ob_get_clean();
	}
}
