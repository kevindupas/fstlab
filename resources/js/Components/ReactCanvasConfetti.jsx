import React from "react";

export default function Confetti({
    angle = 90,
    spread = 65,
    startVelocity = 45,
    elementCount = 200,
    width = "10px",
    height = "10px",
    perspective = "",
    colors = ["#a864fd", "#29cdff", "#78ff44", "#ff718d", "#fdff6a"],
    duration = 4000,
    dragFriction = 0.1,
    random = Math.random,
    onComplete = () => false,
}) {
    const requestRef = React.useRef();
    const startTime = React.useRef();
    const root = React.useRef(null);
    const [particles, setParticles] = React.useState(
        [...Array(elementCount)].map((item, index) => ({
            physics: randomPhysics(angle, spread, startVelocity, random),
            style: {
                transform: "",
                opacity: 0,
            },
        }))
    );
    const parentNodeRect =
        root.current && root.current.parentNode.getBoundingClientRect();

    const animate = (time) => {
        if (!startTime.current) {
            startTime.current = time;
        }

        const progress =
            startTime.current === time
                ? 0
                : (time - startTime.current) / duration;
        setParticles(
            particles.map((particle) =>
                updateParticle(particle, progress, dragFriction)
            )
        );

        if (time - startTime.current < duration) {
            requestRef.current = requestAnimationFrame(animate);
        } else {
            onComplete();
        }
    };

    React.useEffect(() => {
        requestRef.current = requestAnimationFrame(animate);
        return () => cancelAnimationFrame(requestRef.current);
    }, []); // Make sure the effect runs only once

    return (
        <div
            ref={root}
            style={{
                perspective,
                position: "fixed",
                top: 0,
                right: 0,
                bottom: 0,
                left: 0,
                pointerEvents: "none",
                overflow: "hidden",
            }}
        >
            <div
                style={{
                    position: "absolute",
                    left:
                        parentNodeRect &&
                        parentNodeRect.left + parentNodeRect.width / 2,
                    top:
                        parentNodeRect &&
                        parentNodeRect.top + parentNodeRect.height / 2,
                }}
            >
                {particles
                    .filter((particle) => particle.style.opacity > 0)
                    .map((particle, index) => (
                        <div
                            key={index}
                            style={{
                                ...particle.style,
                                width,
                                height,
                                position: "absolute",
                                willChange: "transform, opacity",
                                backgroundColor: colors[index % colors.length],
                            }}
                        />
                    ))}
            </div>
        </div>
    );
}

// randomPhysics and updateParticle from https://github.com/daniel-lundin/dom-confetti 🙂
function randomPhysics(angle, spread, startVelocity, random) {
    const radAngle = angle * (Math.PI / 180);
    const radSpread = spread * (Math.PI / 180);
    return {
        x: 0,
        y: 0,
        z: 0,
        wobble: random() * 10,
        wobbleSpeed: 0.1 + random() * 0.1,
        velocity: startVelocity * 0.5 + random() * startVelocity,
        angle2D: -radAngle + (0.5 * radSpread - random() * radSpread),
        angle3D: -(Math.PI / 4) + random() * (Math.PI / 2),
        tiltAngle: random() * Math.PI,
        tiltAngleSpeed: 0.1 + random() * 0.3,
    };
}

function updateParticle(particle, progress, dragFriction) {
    /* eslint-disable no-param-reassign */
    particle.physics.x +=
        Math.cos(particle.physics.angle2D) * particle.physics.velocity;
    particle.physics.y +=
        Math.sin(particle.physics.angle2D) * particle.physics.velocity;
    particle.physics.z +=
        Math.sin(particle.physics.angle3D) * particle.physics.velocity;
    particle.physics.wobble += particle.physics.wobbleSpeed;
    particle.physics.velocity -= particle.physics.velocity * dragFriction;

    particle.physics.y += 3;
    particle.physics.tiltAngle += particle.physics.tiltAngleSpeed;

    const { x, y, z, tiltAngle, wobble } = particle.physics;
    const wobbleX = x + 10 * Math.cos(wobble);
    const wobbleY = y + 10 * Math.sin(wobble);
    const transform = `translate3d(${wobbleX}px, ${wobbleY}px, ${z}px) rotate3d(1, 1, 1, ${tiltAngle}rad)`;

    particle.style.transform = transform;
    particle.style.opacity = 1 - progress;

    return particle;
    /* eslint-enable */
}
